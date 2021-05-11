<?php

namespace EgalFramework\Auth\Commands;

use EgalFramework\Auth\Models\Service;
use Illuminate\Console\Command;

/**
 * Class CreateRole
 * @package EgalFramework\Auth\Commands
 */
class CreateService extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'auth:create_service {name} {password}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Create new service';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $role = new Service;
        $role->name = $this->argument('name');
        $role->password = $this->argument('password');
        $role->save();
    }

}
