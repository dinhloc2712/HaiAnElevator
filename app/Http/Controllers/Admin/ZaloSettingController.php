<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ZaloService;
use App\Models\ZaloMessageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class ZaloSettingController extends Controller
{
    public function index(ZaloService $zalo)
    {
        $parseDays = function($days) {
            if (empty($days)) return [];
            if (is_string($days)) {
                $days = trim($days);
                if (str_starts_with($days, '[') && str_ends_with($days, ']')) {
                    return array_map('intval', json_decode($days, true) ?? []);
                }
                return array_filter(array_map('intval', explode(',', $days)));
            }
            return array_map('intval', (array)$days);
        };

        $settings = [
            'app_id'                    => $zalo->readEnvValue('ZALO_APP_ID') ?: config('services.zalo.app_id'),
            'app_secret'                => $zalo->readEnvValue('ZALO_APP_SECRET') ?: config('services.zalo.app_secret'),
            'oa_id'                     => $zalo->readEnvValue('ZALO_OA_ID') ?: config('services.zalo.oa_id'),
            'access_token'              => $zalo->readEnvValue('ZALO_ACCESS_TOKEN') ?: config('services.zalo.access_token'),
            'refresh_token'             => $zalo->readEnvValue('ZALO_REFRESH_TOKEN') ?: config('services.zalo.refresh_token'),
            'maintenance_template_id'   => $zalo->readEnvValue('ZALO_MAINTENANCE_TEMPLATE_ID') ?: config('services.zalo.maintenance_template_id'),
            'maintenance_days_before'   => $parseDays($zalo->readEnvValue('ZALO_MAINTENANCE_DAYS_BEFORE') ?: config('services.zalo.maintenance_days_before')),
            'inspection_template_id'    => $zalo->readEnvValue('ZALO_INSPECTION_TEMPLATE_ID') ?: config('services.zalo.inspection_template_id'),
            'inspection_days_before'    => $parseDays($zalo->readEnvValue('ZALO_INSPECTION_DAYS_BEFORE') ?: config('services.zalo.inspection_days_before')),
        ];

        $logs = ZaloMessageLog::latest()->take(15)->get();

        return view('admin.zalo.settings', compact('settings', 'logs'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_id'                    => 'required|string',
            'app_secret'                => 'required|string',
            'oa_id'                     => 'required|string',
            'access_token'              => 'nullable|string',
            'refresh_token'             => 'nullable|string',
            'maintenance_template_id'   => 'nullable|string|max:50',
            'maintenance_days_before'   => 'nullable|array',
            'maintenance_days_before.*' => 'nullable|integer|in:1,3,7',
            'inspection_template_id'    => 'nullable|string|max:50',
            'inspection_days_before'    => 'nullable|array',
            'inspection_days_before.*'  => 'nullable|integer|in:1,3,7',
        ]);

        $maintenance_days = array_map('intval', $request->input('maintenance_days_before', []));
        $inspection_days = array_map('intval', $request->input('inspection_days_before', []));

        $data = [
            'ZALO_APP_ID'                   => $request->app_id,
            'ZALO_APP_SECRET'               => $request->app_secret,
            'ZALO_OA_ID'                    => $request->oa_id,
            'ZALO_ACCESS_TOKEN'             => $request->access_token,
            'ZALO_REFRESH_TOKEN'            => $request->refresh_token,
            'ZALO_MAINTENANCE_TEMPLATE_ID'  => $request->maintenance_template_id,
            'ZALO_MAINTENANCE_DAYS_BEFORE'  => implode(',', $maintenance_days),
            'ZALO_INSPECTION_TEMPLATE_ID'   => $request->inspection_template_id,
            'ZALO_INSPECTION_DAYS_BEFORE'   => implode(',', $inspection_days),
        ];

        $this->updateEnvFile($data);

        try {
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        } catch (\Exception $e) {
            // Ignore if config:clear fails
        }

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
