<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ZaloSettingController extends Controller
{
    public function index()
    {
        $settings = [
            'app_id' => config('services.zalo.app_id'),
            'app_secret' => config('services.zalo.app_secret'),
            'oa_id' => config('services.zalo.oa_id'),
            'template_id' => config('services.zalo.template_id'),
            'access_token' => config('services.zalo.access_token'),
            'refresh_token' => config('services.zalo.refresh_token'),
        ];

        return view('admin.zalo.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_id' => 'required|string',
            'app_secret' => 'required|string',
            'oa_id' => 'required|string',
            'template_id' => 'nullable|string',
            'access_token' => 'nullable|string',
            'refresh_token' => 'nullable|string',
        ]);

        $data = [
            'ZALO_APP_ID' => $request->app_id,
            'ZALO_APP_SECRET' => $request->app_secret,
            'ZALO_OA_ID' => $request->oa_id,
            'ZALO_TEMPLATE_ID' => $request->template_id,
            'ZALO_ACCESS_TOKEN' => $request->access_token,
            'ZALO_REFRESH_TOKEN' => $request->refresh_token,
        ];

        $this->updateEnvFile($data);

        return back()->with('success', 'Đã cập nhật cấu hình Zalo Business Service.');
    }

    public function testSend(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
        ]);

        // Create a dummy object to mimic an elevator for the notification
        $elevator = (object)[
            'id' => 0,
            'code' => 'TEST-001',
            'customer_name' => 'Khách hàng Thử nghiệm',
            'customer_phone' => $request->phone,
            'maintenance_deadline' => now()->addDays(3),
            'address' => '123 Đường Thử Nghiệm',
            'district' => 'Quận 1',
            'province' => 'TP. Hồ Chí Minh',
            'building' => (object)['name' => 'Tòa nhà Demo']
        ];

        try {
            // Using Laravel's on-demand notification to send only to the Zalo channel
            \Illuminate\Support\Facades\Notification::route('zalo', $request->phone)
                ->notify(new \App\Notifications\MaintenanceReminderNotification($elevator));

            return back()->with('success', 'Đã gửi yêu cầu test tới Zalo. Vui lòng kiểm tra điện thoại: ' . $request->phone);
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi khi gửi test: ' . $e->getMessage());
        }
    }

    protected function updateEnvFile(array $data)
    {
        $path = base_path('.env');
        if (!file_exists($path)) return;

        $content = file_get_contents($path);

        foreach ($data as $key => $value) {
            // Strip any existing surrounding quotes first
            $value = trim($value, '"\'');
            // If the key exists, update it. If not, append it.
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        file_put_contents($path, $content);
    }

    /**
     * Exchange authorization code for tokens (manual flow)
     */
    public function exchangeCode(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $appId = config('services.zalo.app_id');
        $appSecret = config('services.zalo.app_secret');

        try {
            $response = Http::asForm()->withHeaders([
                'secret_key' => $appSecret
            ])->post('https://oauth.zaloapp.com/v4/oa/access_token', [
                'code' => $request->code,
                'app_id' => $appId,
                'grant_type' => 'authorization_code',
            ]);

            $data = $response->json();

            if (isset($data['access_token'])) {
                $this->updateEnvFile([
                    'ZALO_ACCESS_TOKEN' => $data['access_token'],
                    'ZALO_REFRESH_TOKEN' => $data['refresh_token'],
                ]);
                return back()->with('success', 'Lấy Token thành công! Access Token và Refresh Token đã được cập nhật tự động.');
            }

            return back()->with('error', 'Lỗi khi đổi mã: ' . ($data['error_description'] ?? json_encode($data)));
        } catch (\Exception $e) {
            return back()->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }

    /**
     * Redirect to Zalo for authorization
     */
    public function redirect()
    {
        $appId = config('services.zalo.app_id');
        $redirectUri = route('admin.zalo.settings.callback');
        
        $url = "https://oauth.zaloapp.com/v4/oa/permission?app_id={$appId}&redirect_uri=" . urlencode($redirectUri);
        
        return redirect($url);
    }

    /**
     * Handle Zalo callback and exchange code for tokens
     */
    public function callback(Request $request)
    {
        if (!$request->code) {
            return redirect()->route('admin.zalo.settings')->with('error', 'Không nhận được mã xác thực từ Zalo.');
        }

        $appId = config('services.zalo.app_id');
        $appSecret = config('services.zalo.app_secret');

        try {
            $response = Http::asForm()->withHeaders([
                'secret_key' => $appSecret
            ])->post('https://oauth.zaloapp.com/v4/oa/access_token', [
                'code' => $request->code,
                'app_id' => $appId,
                'grant_type' => 'authorization_code',
            ]);

            $data = $response->json();

            if (isset($data['access_token'])) {
                $this->updateEnvFile([
                    'ZALO_ACCESS_TOKEN' => $data['access_token'],
                    'ZALO_REFRESH_TOKEN' => $data['refresh_token'],
                ]);

                return redirect()->route('admin.zalo.settings')->with('success', 'Đã kết nối Zalo OA và lấy mã thành công!');
            }

            return redirect()->route('admin.zalo.settings')->with('error', 'Lỗi khi đổi mã: ' . ($data['error_description'] ?? 'Không xác định'));

        } catch (\Exception $e) {
            return redirect()->route('admin.zalo.settings')->with('error', 'Lỗi hệ thống: ' . $e->getMessage());
        }
    }
}
