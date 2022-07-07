<?php

namespace Egal\Core\Auth\Commands;

use Egal\Core\Auth\Role;
use Illuminate\Console\Command;

class ListRolesCommand extends Command
{
    protected $signature = 'egal:auth:list-roles';

    protected $description = 'List all roles';

    public function handle()
    {
        $headers = ['Name', 'Is Default'];
        $roles = Role::all(['name', 'is_default'])->toArray();

        $this->table($headers, $roles);
    }
}
