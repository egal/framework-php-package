<?php

namespace Egal\Core\Auth\Commands;

use Egal\Core\Auth\Role;
use Egal\Core\Auth\User;
use Egal\Core\Auth\UserRole;
use Illuminate\Console\Command;

class UnsetUserRoleCommand extends Command
{
    protected $signature = 'egal:auth:unset-user-role
        {userEmail :     The email of the user}
        {roleName:       The name of the role}';

    protected $description = 'Unset role for user';

    public function handle()
    {
        $user = User::query()->where('email', '=', $this->argument('userEmail'))->first();
        $role = Role::query()->where('name', '=', $this->argument('roleName'))->first();

        if (!$user) {
            $this->info("User with email `{$this->argument('userEmail')}` ". "not exist");
        } elseif (!$role) {
            $this->info("Role with name `{$this->argument('roleName')}` ". "not exist");
        } else {
            $userRole = UserRole::query()
                ->where('user_id', '=', $user->id)
                ->where('role_id', '=', $role->id)
                ->first();

            if ($userRole) {
                $userRole->delete();
                $this->info("Role `{$role->name}` ". "unset for user with email " . "'{$user->email}'");
            } else {
                $this->info("Role `{$role->name}` ". "not been assigned for user with email " . "'{$user->email}'");
            }
        }
    }
}
