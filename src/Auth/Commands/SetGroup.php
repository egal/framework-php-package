<?php

namespace EgalFramework\Auth\Commands;

use EgalFramework\Auth\Models\Role;
use EgalFramework\Auth\Models\RoleService;
use EgalFramework\Auth\Models\RoleUser;
use EgalFramework\Auth\Models\Service;
use EgalFramework\Auth\Models\User;
use Exception;
use Illuminate\Console\Command;

/**
 * Class SetGroup
 * @package EgalFramework\Auth\Commands
 */
class SetGroup extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'auth:set_group {email}, {int_name} {is_service?}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Set role to user';

    /**
     * Execute the console command.
     * @throws Exception
     */
    public function handle()
    {
        if ((bool)$this->argument('is_service')) {
            $this->processService();
        } else {
            $this->processUser();
        }
    }

    /**
     * @throws Exception
     */
    private function processService(): void
    {
        $service = Service::where('name', $this->argument('email'))->first();
        if (!$service) {
            throw new Exception('Service not found');
        }
        $role = Role::where('internal_name', $this->argument('int_name'))->first();
        if (!$role) {
            throw new Exception('Role not found');
        }
        $group = new RoleService;
        $group->service_id = $service->id;
        $group->role_id = $role->id;
        $group->save();
    }

    /**
     * @throws Exception
     */
    private function processUser(): void
    {
        $user = User::where('email', $this->argument('email'))->first();
        if (!$user) {
            throw new Exception('User not found');
        }
        $role = Role::where('internal_name', $this->argument('int_name'))->first();
        if (!$role) {
            throw new Exception('Role not found');
        }
        $group = new RoleUser();
        $group->user_id = $user->id;
        $group->role_id = $role->id;
        $group->save();
    }

}
