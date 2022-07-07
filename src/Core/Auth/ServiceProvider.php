<?php

namespace Egal\Core\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;

class ServiceProvider extends IlluminateServiceProvider
{
    public function register()
    {
        if (class_exists('Egal\Core\Auth\RouteServiceProvider')) {
            $this->app->register('Egal\Core\Auth\RouteServiceProvider');
        }

        if (class_exists('Egal\Core\Auth\CommandsServiceProvider')) {
            $this->app->register('Egal\Core\Auth\CommandsServiceProvider');
        }
    }

    public function boot () {

        $this->loadRoutesFrom(__DIR__.'/../routes/auth-routes.php');

        Auth::extend('jwt_token', function ($app, $name, array $config) {
            $request = app('request');

            return new JwtTokenGuard($request);
        });

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__ . '/config/auth.php' => config_path('auth.php'),
            ], 'config');

            if (! class_exists('CreateUsersTable')) {
                $this->publishes([
                    __DIR__ . '/database/migrations/create_users_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_users_table.php'),
                ], 'migrations');
            }
            if (! class_exists('CreateRolesTable')) {
                $this->publishes([
                    __DIR__ . '/database/migrations/create_roles_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_roles_table.php'),
                ], 'migrations');
            }
            if (! class_exists('CreateUserRolesTable')) {
                $this->publishes([
                    __DIR__ . '/database/migrations/create_user_roles_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time() + 1) . '_create_user_roles_table.php'),
                ], 'migrations');
            }
        }
    }
}
