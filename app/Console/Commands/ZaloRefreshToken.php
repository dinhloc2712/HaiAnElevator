<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ZaloService;
use Illuminate\Support\Facades\Log;

class ZaloRefreshToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zalo:refresh-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically refresh Zalo Access Token and Refresh Token';

    /**
     * Execute the console command.
     */
    public function handle(ZaloService $zalo)
    {
        $this->info('Starting Zalo token refresh...');
        
        try {
            $newToken = $zalo->refreshAccessToken();
            
            if ($newToken) {
                $this->info('Zalo Token refreshed successfully!');
                $this->line('New Access Token (first 20 chars): ' . substr($newToken, 0, 20) . '...');
                Log::info('Zalo Token automatically refreshed via Cronjob.');
            } else {
                $this->error('Failed to refresh Zalo Token.');
            }
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            Log::error('Zalo Token Automatic Refresh Failed: ' . $e->getMessage());
        }
    }
}
