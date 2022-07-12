<?php

namespace Egal\Core\Auth\Commands;

use Egal\Core\Auth\Role;
use Egal\Core\Auth\UserRole;
use Egal\Core\Facades\AuthManager;
use Illuminate\Console\Command;

class SetUserRoleCommand extends Command
{
    protected $signature = 'egal:auth:set-user-role
        {userEmail :     The email of the user}
        {roleName :       The name of the role}';

    protected $description = 'Set role for user';

    public function handle()
    {
        $user = AuthManager::newUser()->newQuery()->where('email', '=', $this->argument('userEmail'))->first();
        $role = Role::query()->where('name', '=', $this->argument('roleName'))->first();

        if (!$user) {
            $this->info("User with email `{$this->argument('userEmail')}` ". "not exist");
        } elseif (!$role) {
            $this->info("Role with name `{$this->argument('roleName')}` ". "not exist");
        } else {
            UserRole::query()
                ->create(['user_id' => $user->id, 'role_id' => $role->id]);

            $this->info("Role `{$role->name}` ". "set for user with email " . "'{$user->email}'");
        }
    }
}
