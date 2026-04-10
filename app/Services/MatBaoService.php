<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;

class MatBaoService
{
    private $baseUrl = 'https://demo-api-econtract-mbc.matbao.in'; // Move to config/env later if needed for prod
    private $taxcode;
    private $username;
    private $password;
    private $signatureImageBase64 = '';

    public function __construct(User $user)
    {
        if (empty($user->matbao_taxcode) || empty($user->matbao_username) || empty($user->matbao_password)) {
            throw new Exception("Thông tin cấu hình Mắt Bão CA của tài khoản chưa đầy đủ.");
        }

        $this->taxcode = $user->matbao_taxcode;
        $this->username = $user->matbao_username;
        $this->password = $user->matbao_password;
        
        if (!empty($user->matbao_signature_image) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->matbao_signature_image)) {
            $imageContent = \Illuminate\Support\Facades\Storage::disk('public')->get($user->matbao_signature_image);
            $this->signatureImageBase64 = base64_encode($imageContent);
        }
    }

    /**
     * Get Authentication Token
     */
    private function getToken()
    {
        $response = Http::withoutVerifying()->post("{$this->baseUrl}/auth/token-matbaoca", [
            'taxcode' => $this->taxcode,
            'username' => $this->username,
            'password' => $this->password
        ]);

        $data = $response->json();
        
        // Postman shows it returns the token string directly if successful, but usually it might be wrapped.
        // Let's assume standard object based on typical responses, or direct string.
        // If it's a string, $response->json() might be null or the direct string.
        if ($response->successful()) {
             // Handle both plaintext token response and JSON wrapped response just in case
             if(is_array($data) && isset($data['token'])) {
                 return $data['token'];
             } else if(is_array($data) && isset($data['Data'])) {
                 return $data['Data'];
             } else if(is_string($response->body())) {
                 $body = json_decode($response->body(), true);
                 if(is_array($body) && isset($body['Token'])) return $body['Token'];
                 if(is_array($body) && isset($body['Data'])) return $body['Data'];
                 
                 // If the response is literally just the raw token string
                 if(!empty($response->body()) && strlen($response->body()) > 20 && !str_starts_with($response->body(), '{')) {
                     return trim($response->body(), '"'); // some APIs return "token" with quotes
                 }
             }
        }

        Log::error('MatBao CA Login Failed: ' . $response->body());
        throw new Exception("Đăng nhập Mắt Bão CA thất bại. Vui lòng kiểm tra lại cấu hình.");
    }

    /**
     * Sign PDF synchronously
     * @param string $pdfBase64 The Base64 string of the original PDF
     * @param array $config Visual configuration for the signature
     * @return string The Base64 string of the signed PDF
     */
    public function signPdf(string $pdfBase64, array $config = [])
    {
        // 1. Get Token
        $token = $this->getToken();

        // 2. Prepare default visual config (approximate stamp box in Top-Right edge)
        $defaultConfig = [
            'PdfBase64' => $pdfBase64,
            'RecImgBase64' => $this->signatureImageBase64, // Required by API, but empty may be accepted if auto-generated, or we need a real base64 stamp. We will use a generic invisible or small placeholder if it throws an error.
            'RecWidth' => 200,
            'RecHeight' => 70,
            'ArisingImgBase64' => '',
            'ArisingImgWidth' => 0,
            'ArisingImgHeight' => 0,
            'ArisingPosition' => 0,
            'ArisingPositionAlign' => 0,
            'Page' => 1,
            'X' => 350,
            'Y' => 100, // Adjusted Y downward
            'VerifiedByLabel' => 'Xác thực bởi:',
            'SignedByLabel' => 'Người ký:',
            'SignedDateLabel' => 'Ngày ký:',
            'SignedDateFormat' => 'dd/MM/yyyy HH:mm:ss',
            'AlignmentVerifiedByLabel' => 'left',
            'AlignmentSignedByLabel' => 'left',
            'AlignmentSignedDateLabel' => 'left',
            'PositionSignedByLabel' => 1,
            'PositionSignedDateLabel' => 2,
            'PositionVerifiedByLabel' => 0,
        ];

        $payload = array_merge($defaultConfig, $config);

        // 3. Call Sign API
        $response = Http::withoutVerifying()->withToken($token)
            ->post("{$this->baseUrl}/signing-matbaoca/signature-pdf", $payload);

        if ($response->successful()) {
            $data = $response->json();
            
            if (isset($data['Success']) && $data['Success'] === true && !empty($data['Data'])) {
                return $data['Data']; // The Base64 of the signed PDF
            } else {
                Log::error('MatBao CA Sign Error (Success = false): ' . $response->body());
                $errorCode = $data['ErrorCode'] ?? 'Unknown Error';
                throw new Exception("Lỗi ký số Mắt Bão CA: Mã lỗi " . $errorCode);
            }
        }

        Log::error('MatBao CA Sign HTTP Error: ' . $response->body());
        throw new Exception("Lỗi kết nối tới hệ thống Mắt Bão CA.");
    }
}
