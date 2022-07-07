<?php

namespace Egal\Core\Auth\Commands;

use Egal\Core\Auth\Role;
use Illuminate\Console\Command;

class CreateRoleCommand extends Command
{
    protected $signature = 'egal:auth:create-role
        {name :     The name of the role}
        {--default : Whether the role should be default}';

    protected $description = 'Create a role';

    public function handle()
    {
        $role = Role::query()
            ->where('name', '=', $this->argument('name'))
            ->first();

        if ($role) {
            $this->info("Role `{$role->name}` ". "already exists");
        } else {
            Role::query()
                ->create(['name' => $this->argument('name'), 'is_default' => $this->option('default')]);

            $this->info("Role `{$role->name}` ". "created");
        }
    }
}
