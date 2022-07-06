<?php

namespace Egal\Core\Auth;

use Illuminate\Console\Command;

class CreateRoleCommand extends Command
{
    protected $signature = 'egal:create-role
        {name : The name of the role}';

    protected $description = 'Create a role';

    public function handle()
    {
        $role = Role::findOrCreate($this->argument('name'));

        $this->info("Role `{$role->name}` ". "created");
    }
}
