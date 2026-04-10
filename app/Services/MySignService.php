<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Viettel MySign - Ký số truyền file (File Signing)
 *
 * Flow:
 *  1. login()       → access_token
 *  2. uploadFile()  → file_id
 *  3. signFile()    → transactionId
 *  4. checkSignStatus()  → status & signatures
 *  5. checkFileStatus()  → url file đã ký
 *  6. downloadSignedFile() → binary PDF content
 */
class MySignService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $profileId;
    protected string $userId;
    protected string $credentialId;

    // Cache key prefix (per-user để không lẫn token)
    protected string $cacheKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('mysign.base_url', 'https://remotesigning.viettel.vn'), '/');
    }

    /**
     * Gán thông tin tài khoản ký số theo user
     */
    public function setUser(\App\Models\User $user): static
    {
        if (empty($user->mysign_client_id) || empty($user->mysign_client_secret) || empty($user->mysign_user_id)) {
            throw new \Exception('Tài khoản của bạn chưa được cấu hình thông tin ký số Viettel MySign. Vui lòng liên hệ Admin.');
        }

        $this->clientId     = $user->mysign_client_id;
        $this->clientSecret = $user->mysign_client_secret;
        $this->profileId    = $user->mysign_profile_id ?? 'adss:ras:profile:001';
        $this->userId       = $user->mysign_user_id;
        $this->credentialId = $user->mysign_credential_id ?? '';
        $this->cacheKey     = 'mysign_token_user_' . $user->id;

        return $this;
    }

    // -------------------------------------------------------------------------
    // BƯỚC 1: Đăng nhập lấy access_token
    // POST /vtss/service/ras/v1/login
    // -------------------------------------------------------------------------

    /**
     * Lấy access_token (cache 58 phút, token sống 1 giờ)
     */
    public function getToken(): string
    {
        return Cache::remember($this->cacheKey, 3480, function () {
            $response = Http::withoutVerifying()
                ->post($this->baseUrl . '/vtss/service/ras/v1/login', [
                    'client_id'     => $this->clientId,
                    'user_id'       => $this->userId,
                    'client_secret' => $this->clientSecret,
                    'profile_id'    => $this->profileId,
                ]);

            if ($response->successful() && $response->json('access_token')) {
                return $response->json('access_token');
            }

            Log::error('MySign Login Error', [
                'status'   => $response->status(),
                'response' => $response->body(),
            ]);
            throw new \Exception('Không thể kết nối đến cổng ký số Viettel MySign. Vui lòng kiểm tra lại cấu hình.');
        });
    }

    /**
     * Xóa token cache (dùng khi cần force refresh)
     */
    public function forgetToken(): void
    {
        Cache::forget($this->cacheKey);
    }

    // -------------------------------------------------------------------------
    // BƯỚC 2: Upload file lên server ký
    // POST /vtss/service/external/upload (multipart/form-data)
    // field "request" = JSON string, field "file" = binary PDF
    // Response: { "file_id": "561", "fileName": "...", ... }
    // -------------------------------------------------------------------------

    /**
     * Upload file PDF lên server ký
     *
     * @param string $filePath   Đường dẫn tuyệt đối đến file PDF
     * @param string $docName    Tên tài liệu hiển thị
     * @param array  $display    Vị trí hiển thị chữ ký, mặc định trang 1
     * @param string|null $signatureImageBase64  Ảnh chữ ký (base64) nếu có
     * @param int $renderType Phương thức hiển thị chữ ký (1: text, 2: text+logo trái, 3: logo, 4: text+logo trên, 5: text+background)
     * @return int  file_id trả về từ server
     */
    public function uploadFile(
        string $filePath,
        string $docName = 'Tài liệu',
        array  $display = [],
        ?string $signatureImageBase64 = null,
        int $renderType = 2
    ): int {
        $token = $this->getToken();

        if (empty($display)) {
            $display = [[
                'page'            => 1,
                'coorX'           => 10,
                'coorY'           => 10,
                'widthRectangle'  => 400,
                'heightRectangle' => 100,
            ]];
        }

        $requestJson = [
            'render'        => $renderType,       // 1=text, 2=text+logo trái, 3=logo, 4=text+logo trên, 5=text+background
            'fontType'      => 1,
            'fontSize'      => 12,
            'reason'        => 'Ký số điện tử',
            'location'      => 'Việt Nam',
            'document_name' => $docName,
            'display'       => $display,
        ];

        if ($signatureImageBase64) {
            $requestJson['base64Image'] = $signatureImageBase64;
        }

        // Phải gửi multipart: 'request' là text field (Content-Type: text/plain),
        // 'file' là binary PDF — dùng Guzzle multipart trực tiếp
        $attempt = 0;
        retry:
        try {
            $client   = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->post($this->baseUrl . '/vtss/service/external/upload', [
                'headers'   => ['Authorization' => 'Bearer ' . $token],
                'multipart' => [
                    [
                        'name'     => 'request',
                        'contents' => json_encode($requestJson),
                        'headers'  => ['Content-Type' => 'text/plain'],
                    ],
                    [
                        'name'     => 'file',
                        'contents' => fopen($filePath, 'r'),
                        'filename' => basename($filePath),
                        'headers'  => ['Content-Type' => 'application/pdf'],
                    ],
                ],
            ]);
            $body   = json_decode((string) $response->getBody(), true);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            // If 401 Unauthorized → token expired server-side, clear cache & retry once
            if ($attempt === 0 && $e->hasResponse() && $e->getResponse()->getStatusCode() === 401) {
                $this->forgetToken();
                $token = $this->getToken();
                $attempt++;
                goto retry;
            }
            $body = $e->hasResponse()
                ? json_decode((string) $e->getResponse()->getBody(), true)
                : [];
            Log::error('MySign Upload Error', ['msg' => $e->getMessage()]);
            throw new \Exception('Upload file lên server ký thất bại: ' . ($e->getMessage()));
        }

        $fileId = $body['file_id'] ?? null;
        if ($fileId !== null) {
            return (int) $fileId;
        }

        Log::error('MySign Upload Error', ['response' => $body]);
        throw new \Exception('Upload file thất bại, server không trả về file_id. Response: ' . json_encode($body));
    }

    // -------------------------------------------------------------------------
    // BƯỚC 3: Gửi yêu cầu ký file
    // POST /vtss/service/external/signing
    // Body: { "file_id": [id], "credential_id": "...", "description": "..." }
    // Response: { "transactionId": "uuid" }
    // -------------------------------------------------------------------------

    /**
     * Gửi yêu cầu ký một hoặc nhiều file
     *
     * @param int|int[] $fileIds   file_id hoặc mảng file_id
     * @param string    $description  Mô tả giao dịch
     * @return string  transactionId
     */
    public function signFile(int|array $fileIds, string $description = 'Ký số tài liệu'): string
    {
        $token = $this->getToken();

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->post($this->baseUrl . '/vtss/service/external/signing', [
                'file_id'       => (array) $fileIds,
                'credential_id' => $this->credentialId,
                'description'   => $description,
            ]);

        if ($response->successful() && $response->json('transactionId')) {
            return $response->json('transactionId');
        }

        Log::error('MySign Sign Error', [
            'file_ids' => $fileIds,
            'status'   => $response->status(),
            'response' => $response->body(),
        ]);
        throw new \Exception('Gửi yêu cầu ký số thất bại: ' . $response->body());
    }

    // -------------------------------------------------------------------------
    // BƯỚC 4: Kiểm tra trạng thái giao dịch ký
    // POST /vtss/service/requests/status
    // Body: { "transactionId": "uuid" }
    // Response: { "status": "1", "description": "Ký thành công", "signatures": [...] }
    // status: "1" = thành công, "0" = đang xử lý, khác = lỗi
    // -------------------------------------------------------------------------

    /**
     * Kiểm tra trạng thái giao dịch ký
     *
     * @return array  ['status' => '1'|'0'|..., 'description' => '...', 'signatures' => [...]]
     */
    public function checkSignStatus(string $transactionId): array
    {
        $token = $this->getToken();

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->post($this->baseUrl . '/vtss/service/requests/status', [
                'transactionId' => $transactionId,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('MySign CheckSignStatus Error', [
            'transactionId' => $transactionId,
            'status'        => $response->status(),
            'response'      => $response->body(),
        ]);
        throw new \Exception('Không thể kiểm tra trạng thái giao dịch ký: ' . $response->body());
    }

    // -------------------------------------------------------------------------
    // BƯỚC 5: Kiểm tra trạng thái file sau khi ký
    // POST /vtss/service/external/file/status
    // Body: { "file_id": 561, "transactionId": "uuid" }
    // Response: { "status": 1, "message": "Ký thành công", "url": "http://..." }
    // -------------------------------------------------------------------------

    /**
     * Kiểm tra trạng thái file đã ký và lấy URL download
     *
     * @return array  ['status' => 1, 'message' => '...', 'url' => '...']
     */
    public function checkFileStatus(int $fileId, string $transactionId): array
    {
        $token = $this->getToken();

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->post($this->baseUrl . '/vtss/service/external/file/status', [
                'file_id'       => $fileId,
                'transactionId' => $transactionId,
            ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('MySign CheckFileStatus Error', [
            'file_id'       => $fileId,
            'transactionId' => $transactionId,
            'status'        => $response->status(),
            'response'      => $response->body(),
        ]);
        throw new \Exception('Không thể kiểm tra trạng thái file đã ký: ' . $response->body());
    }

    // -------------------------------------------------------------------------
    // BƯỚC 6: Tải file đã ký về
    // GET /vtss/service/external/download/{file_id}
    // Response: binary PDF
    // -------------------------------------------------------------------------

    /**
     * Tải binary content của file PDF đã ký
     *
     * @return string  Nội dung binary của file PDF
     */
    public function downloadSignedFile(int $fileId): string
    {
        $token = $this->getToken();

        $response = Http::withoutVerifying()
            ->withToken($token)
            ->withHeaders(['Accept' => 'application/pdf'])
            ->get($this->baseUrl . '/vtss/service/external/download/' . $fileId);

        if ($response->successful()) {
            return $response->body();
        }

        Log::error('MySign Download Error', [
            'file_id'  => $fileId,
            'status'   => $response->status(),
            'response' => substr($response->body(), 0, 500),
        ]);
        throw new \Exception('Tải file đã ký thất bại. HTTP ' . $response->status());
    }

    // -------------------------------------------------------------------------
    // HELPER: Ký file full-flow (upload → sign → poll → download content)
    // -------------------------------------------------------------------------

    /**
     * Ký file hoàn chỉnh một lần gọi.
     * Lưu ý: polling status sẽ thử tối đa $maxPolls lần, mỗi lần cách $pollInterval giây.
     * Nếu MobileID / OTP cần xác nhận trên điện thoại → status "0" sẽ được poll đợi.
     *
     * @param string $filePath   Đường dẫn file PDF gốc
     * @param string $docName    Tên tài liệu
     * @param array  $display    Vị trí chữ ký
     * @param int    $maxPolls   Số lần poll tối đa (default 20 = 60s)
     * @param int    $pollInterval  Giây giữa các lần poll (default 3)
     * @param int    $renderType Phương thức hiển thị (1-5)
     * @return array  ['file_id' => int, 'transactionId' => string, 'pdfContent' => string]
     */
    public function signFileFull(
        string $filePath,
        string $docName = 'Tài liệu',
        array  $display = [],
        int    $maxPolls = 20,
        int    $pollInterval = 3,
        int    $renderType = 2
    ): array {
        // B1: Upload
        $fileId = $this->uploadFile($filePath, $docName, $display, null, $renderType);

        // B2: Sign
        $transactionId = $this->signFile($fileId, 'Ký số: ' . $docName);

        // B3: Poll sign status
        $signResult = null;
        for ($i = 0; $i < $maxPolls; $i++) {
            sleep($pollInterval);
            $signResult = $this->checkSignStatus($transactionId);
            if (($signResult['status'] ?? '') === '1') {
                break;
            }
            if (($signResult['status'] ?? '') !== '0') {
                // Lỗi hoặc từ chối
                throw new \Exception('Ký số thất bại: ' . ($signResult['description'] ?? 'Không rõ lỗi'));
            }
        }

        if (($signResult['status'] ?? '') !== '1') {
            throw new \Exception('Hết thời gian chờ xác nhận ký số. Vui lòng thử lại.');
        }

        // B4: Check file status để xác nhận file đã được apply chữ ký
        $fileStatus = $this->checkFileStatus($fileId, $transactionId);
        if (($fileStatus['status'] ?? 0) !== 1) {
            throw new \Exception('File chưa được ký xong: ' . ($fileStatus['message'] ?? ''));
        }

        // B5: Download binary
        $pdfContent = $this->downloadSignedFile($fileId);

        return [
            'file_id'       => $fileId,
            'transactionId' => $transactionId,
            'pdfContent'    => $pdfContent,
        ];
    }
}
