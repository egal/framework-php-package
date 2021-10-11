<?php

declare(strict_types=1);

namespace Egal\AuthServiceDependencies\Commands;

use Illuminate\Console\Command;

/**
 * Register your service in auth-service.
 *
 * @deprecated since v2.0.0, replaced by automatic registration of services
 */
class RegisterServiceCommand extends Command
{

    protected $signature = 'egal:register:service
                            {service_name : APP_SERVICE_NAME of your service that you want register}
                            {service_key : APP_SERVICE_KEY of your service}
                           ';

    protected $description = 'Register new service in auth-service';

    public function handle(): void
    {
        $this->error(
            'This command is deprecated and will be removed since version 2.0.0.'
            . PHP_EOL
            . 'Deprecation\'s reason: Command is replaced by automatic registration of services'
        );
    }

}
