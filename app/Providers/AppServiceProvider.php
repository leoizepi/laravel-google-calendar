<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;
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
        $path_credentials = storage_path('app/google-calendar/service-account.json');
        /*
        if(!file_exists($path_credentials)) {
            dd('arquivo nao encontrado: '.$path_credentials);
        } else {
            //dd('arquivo ok');
        }
        Log::info('GOOGLE_APPLICATION_CREDENTIALS: '.$path_credentials);
        */
        putenv("GOOGLE_APPLICATION_CREDENTIALS=$path_credentials");
    }
}
