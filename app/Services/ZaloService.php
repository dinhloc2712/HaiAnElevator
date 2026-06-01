<?php

namespace App\Services;

use App\Models\ZaloMessageLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ZaloService
{
    protected $appId;
    protected $appSecret;
    protected $oaId;
    protected $accessToken;
    protected $refreshToken;
    protected $baseUrl = 'https://openapi.zalo.me/v2.0/oa';
    protected $znsUrl = 'https://business.openapi.zalo.me/message/template';

    public function __construct()
    {
        $this->appId = config('services.zalo.app_id');
        $this->appSecret = config('services.zalo.app_secret');
        $this->oaId = config('services.zalo.oa_id');
        // Read tokens directly from .env to always get the freshest values
        $this->accessToken = $this->readEnvValue('ZALO_ACCESS_TOKEN') ?: config('services.zalo.access_token');
        $this->refreshToken = $this->readEnvValue('ZALO_REFRESH_TOKEN') ?: config('services.zalo.refresh_token');
    }

    /**
     * Refresh Access Token using Refresh Token
     * Zalo Access Token expires in 25 hours.
     */
    public function refreshAccessToken()
    {
        try {
            $response = Http::asForm()
                ->withHeaders(['secret_key' => $this->appSecret])
                ->post('https://oauth.zaloapp.com/v4/oa/access_token', [
                    'refresh_token' => $this->refreshToken,
                    'app_id' => $this->appId,
                    'grant_type' => 'refresh_token',
                ]);

            $data = $response->json();

            if (isset($data['access_token'])) {
                $this->accessToken = $data['access_token'];
                $this->refreshToken = $data['refresh_token'] ?? $this->refreshToken;

                // Update .env file for persistence (Optional: should use Database for production)
                $this->updateEnvFile([
                    'ZALO_ACCESS_TOKEN' => $this->accessToken,
                    'ZALO_REFRESH_TOKEN' => $this->refreshToken,
                ]);

                return $this->accessToken;
            }

            Log::error('Zalo Token Refresh Failed', $data);
            throw new Exception('Could not refresh Zalo Access Token: ' . ($data['error_description'] ?? 'Unknown error'));

        } catch (Exception $e) {
            Log::error('Zalo Service Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Send ZNS (Zalo Notification Service)
     * @param string $phone Customer phone number (84...)
     * @param string $templateId Registered Template ID
     * @param array $templateData Data for template parameters
     */
    public function sendZNS($phone, $templateId, $templateData)
    {
        // Ensure phone starts with 84
        $phone = $this->formatPhone($phone);

        $trackingId = uniqid('zns_');
        $response = Http::withHeaders(['access_token' => $this->accessToken])
            ->post($this->znsUrl, [
                'phone' => $phone,
                'template_id' => $templateId,
                'template_data' => $templateData,
                'tracking_id' => $trackingId,
            ]);

        $data = $response->json();

        // If token expired or invalid (error -216, -201, or -124), refresh and retry once
        if (isset($data['error']) && in_array($data['error'], [-216, -201, -124])) {
            $this->refreshAccessToken();
            return $this->sendZNS($phone, $templateId, $templateData);
        }

        $status = (!isset($data['error']) || $data['error'] === 0) && $response->successful() ? 'success' : 'failed';
        $errorCode = $data['error'] ?? $response->status();
        $errorMessage = $data['error_description'] ?? $data['error_name'] ?? ($response->successful() ? null : 'HTTP request failed with status ' . $response->status());
        $msgId = data_get($data, 'data.msg_id');

        $this->logMessage([
            'phone' => $phone,
            'channel' => 'zns',
            'template_id' => $templateId,
            'tracking_id' => $trackingId,
            'msg_id' => $msgId,
            'status' => $status,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'response' => [
                'http_status' => $response->status(),
                'data' => $data,
            ],
            'payload' => [
                'template_data' => $templateData,
            ],
        ]);

        if ($status === 'failed') {
            Log::error('ZNS Send Failed', ['phone' => $phone, 'response' => $data, 'payload' => $templateData]);
        } else {
            Log::info('ZNS Sent Successfully', ['phone' => $phone, 'msg_id' => $msgId]);
        }

        return $data;
    }

    /**
     * Send free text message from OA (Only works if user followed OA or interacted recently)
     */
    public function sendOAMessage($userId, $text)
    {
        $response = Http::withHeaders(['access_token' => $this->accessToken])
            ->post("{$this->baseUrl}/message", [
                'recipient' => ['user_id' => $userId],
                'message' => ['text' => $text],
            ]);

        $data = $response->json();
        $status = (!isset($data['error']) || $data['error'] === 0) && $response->successful() ? 'success' : 'failed';
        $errorCode = $data['error'] ?? $response->status();
        $errorMessage = $data['error_description'] ?? $data['error_name'] ?? ($response->successful() ? null : 'HTTP request failed with status ' . $response->status());

        $this->logMessage([
            'phone' => $userId,
            'channel' => 'oa',
            'template_id' => null,
            'tracking_id' => null,
            'msg_id' => data_get($data, 'data.msg_id'),
            'status' => $status,
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'response' => [
                'http_status' => $response->status(),
                'data' => $data,
            ],
            'payload' => ['text' => $text],
        ]);

        if ($status === 'failed') {
            Log::error('OA Message Send Failed', ['user_id' => $userId, 'response' => $data]);
        } else {
            Log::info('OA Message Sent Successfully', ['user_id' => $userId, 'msg_id' => data_get($data, 'data.msg_id')]);
        }

        return $data;
    }

    /**
     * Log the Zalo message send attempt with status and error details.
     */
    protected function logMessage(array $payload)
    {
        try {
            ZaloMessageLog::create($payload);
        } catch (Exception $e) {
            Log::error('Failed to save Zalo message log: ' . $e->getMessage(), ['payload' => $payload]);
        }
    }

    /**
     * Format phone number to Zalo standard (84...)
     */
    protected function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '84' . substr($phone, 1);
        }
        return $phone;
    }

    /**
     * Helper to update .env file
     */
    protected function updateEnvFile(array $data)
    {
        $path = base_path('.env');
        if (!file_exists($path)) return;

        $content = file_get_contents($path);

        foreach ($data as $key => $value) {
            // Strip surrounding quotes that may have been added accidentally
            $value = trim($value, '"\'');
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
        }

        file_put_contents($path, $content);
    }

    /**
     * Read a value directly from .env file (bypasses config cache)
     */
    public function readEnvValue(string $key): ?string
    {
        $path = base_path('.env');
        if (!file_exists($path)) return null;

        $content = file_get_contents($path);
        if (preg_match("/^{$key}=(.*)$/m", $content, $matches)) {
            $value = trim($matches[1]);
            return $value !== '' ? $value : null;
        }
        return null;
    }
}
