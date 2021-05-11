<?php

namespace EgalFramework\Auth\Commands;

use EgalFramework\Auth\Models\Role;
use Illuminate\Console\Command;

/**
 * Class CreateRole
 * @package EgalFramework\Auth\Commands
 */
class CreateRole extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'auth:create_role {name} {internal_name} {is_default?}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Create new role';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $role = new Role();
        $role->name = $this->argument('name');
        $role->internal_name = $this->argument('internal_name');
        $role->is_default = (bool)$this->argument('is_default');
        $role->save();
    }

}
