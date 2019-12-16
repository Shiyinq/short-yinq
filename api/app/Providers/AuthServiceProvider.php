<?php

namespace App\Providers;

use Log;
use \App\Models\User;
use \Firebase\JWT\JWT;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
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
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            
            $authHeader = $request->headers->get('Authorization');
            $authHeader = empty($authHeader) ? $request->get('token') : $authHeader;
            $token = substr($authHeader, strlen('bearer '));
            // var_dump($token);exit;
            
            try {
                $claims = (object) JWT::decode($token, env('JWT_KEY'), ['HS256']);
            }
            catch(\Exception $e) {
                Log::warning('[JWT_ERROR] ' . $e->getMessage());
                return;
            }

            if($claims->iss !== env('APP_NAME')) return;
            
            if($authHeader) {
                return User::where('username', $claims->usr)->first();
            }
        });
    }
}
