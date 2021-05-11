<?php

namespace EgalFramework\Auth\Commands;

use EgalFramework\Auth\Models\User;
use Illuminate\Console\Command;

/**
 * Class CreateRole
 * @package EgalFramework\Auth\Commands
 */
class CreateUser extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'auth:create_user {name} {email} {password}';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Create new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $role = new User;
        $role->name = $this->argument('name');
        $role->email = $this->argument('email');
        $role->password = $this->argument('password');
        $role->save();
    }

}
