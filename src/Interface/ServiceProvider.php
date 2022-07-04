<?php

namespace Egal\Interface;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use function Composer\Autoload\includeFile;

class ServiceProvider extends BaseServiceProvider
{

    public function register()
    {
        $this->app->singleton('egal.interface.metadata.manager', fn($app) => new Manager());
    }

    public function boot()
    {
        // TODO из конфига брать название файла
        require base_path('./interfaces/components.php');
    }

}
