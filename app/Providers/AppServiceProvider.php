<?php

namespace App\Providers;

use Log;
use Storage;
use Google\Client;
use Illuminate\Support\ServiceProvider;

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
        $this->BackupToGoogleDrive();
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
