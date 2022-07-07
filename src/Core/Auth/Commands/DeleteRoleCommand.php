<?php

namespace Egal\Core\Auth\Commands;

use Egal\Core\Auth\Role;
use Illuminate\Console\Command;

class DeleteRoleCommand extends Command
{
    protected $signature = 'egal:auth:delete-role
        {name :     The name of the role}';

    protected $description = 'Delete a role';

    public function handle()
    {
        $role = Role::query()
            ->where('name', '=', $this->argument('name'))
            ->first();

        if ($role) {
            $role->delete();
            $this->info("Role `{$role->name}` ". "deleted");
        } else {
            $this->info("Role `{$this->argument('name')}` ". "not exist");
        }
    }
}
