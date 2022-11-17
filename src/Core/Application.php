<?php

declare(strict_types=1);

namespace Egal\Core;

use Laravel\Lumen\Application as LumenApplication;

class Application extends LumenApplication
{

    /**
     * Get the path to the given configuration file.
     *
     * Rule of search "config":
     * 1. basePath/config
     * 2. egal/egal/core/config
     * 3. laravel/lumen-framework/config
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param string|null $name
     * @return string
     */
    public function getConfigurationPath($name = null): string
    {
        if (!$name) {
            $appConfigDir = $this->basePath('config') . '/';
            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__ . '/config/')) {
                return $path;
            }
        } else {
            $appConfigPath = $this->basePath('config') . '/' . $name . '.php';

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__ . '/config/' . $name . '.php')) {
                return $path;
            }
        }
        return parent::getConfigurationPath($name);
    }

}
