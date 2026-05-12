<?php

namespace App\Providers;

use Google\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Log;
use Storage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->BackupToGoogleDrive();

        // if (!app()->isProduction()) {
            // Mendengarkan semua query database yang dieksekusi
            DB::listen(function ($query) {
                // Properti $query->time menggunakan satuan milidetik (ms)
                // 500 ms = 0.5 detik
                // 5000 ms = 5 detik
                if ($query->time > 5000) {
                    // 1. Ambil riwayat pemanggilan fungsi (backtrace)
                    // Menggunakan DEBUG_BACKTRACE_IGNORE_ARGS agar tidak memakan banyak memori
                    $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
                    $queryLocation = 'Lokasi tidak ditemukan';
    
                    // 2. Loop riwayat tersebut untuk mencari file pertama di luar folder 'vendor'
                    foreach ($traces as $trace) {
                        if (isset($trace['file']) && !str_contains($trace['file'], 'vendor' . DIRECTORY_SEPARATOR)) {
                            // Jika menemukan file aplikasi kita (misal: app/Http/Controllers/UserController.php)
                            $queryLocation = $trace['file'] . ' pada baris ' . $trace['line'];
                            break; // Hentikan loop karena lokasi asal sudah ditemukan
                        }
                    }
                    
                    Log::warning('Slow Query Detected', [
                        // 'sql' => $query->sql,
                        // 'bindings' => $query->bindings,
                        'time' => $query->time . ' ms',
                        'connection' => $query->connectionName,
                        'location' => $queryLocation,
                    ]);
                    Log::warning('SQL Statement', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                    ]);
                }
            });
        // }
    }

    private function BackupToGoogleDrive(){
        try {
            Storage::extend('google', function($app, $config) {
                $options = [];

                // if (!empty($config['teamDriveId'] ?? null)) {
                //     $options['teamDriveId'] = $config['teamDriveId'];
                // }

                if (!empty($config['folderId'] ?? null)) {
                    $options['folderId'] = $config['folderId'];
                }

                $client = new Client();
                $client->setClientId($config['clientId']);
                $client->setClientSecret($config['clientSecret']);
                $client->refreshToken($config['refreshToken']);
                
                $service = new \Google\Service\Drive($client);
                $adapter = new \Masbug\Flysystem\GoogleDriveAdapter($service, $config['folder'] ?? '/', $options);
                $driver = new \League\Flysystem\Filesystem($adapter);

                return new \Illuminate\Filesystem\FilesystemAdapter($driver, $adapter);
            });
        } catch(\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
