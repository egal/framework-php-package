<?php

namespace EgalFramework\CommandLine\Commands;

use EgalFramework\CommandLine\ModelManager;
use EgalFramework\Common\Session;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Class InitAuth
 *
 * This class should be tested manually
 *
 * @codeCoverageIgnore
 * @package EgalFramework\CommandLine\Commands
 */
class InitAuth extends Command
{

    /**
     * The name and signature of the console command.
     * @var string
     */
    protected $signature = 'init_auth';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Init auth project';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = Session::getRegistry()->get('BasePath') . '/composer.json';
        $composer = json_decode(file_get_contents($filePath), true);
        $composer['repositories'] = $this->upgradeRepoList($composer['repositories']);
        if (!isset($composer['require']['egal-framework/auth'])) {
            $composer['require']['egal-framework/auth'] = '@dev';
        }
        file_put_contents($filePath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        shell_exec('composer --working-dir=' . Session::getRegistry()->get('BasePath') . ' update');

        $this->registerModels();
        $this->registerCommands();
    }

    /**
     * @param array $repositories
     * @return mixed
     */
    private function upgradeRepoList(array $repositories)
    {
        foreach ($repositories as $repo) {
            $regex = preg_quote('gitlab.smartworld.team:', '/') . '([0-9]*\/)?'
                . preg_quote('egal-framework/', '/') . '(.+)\.git';
            if (preg_match('/' . $regex . '$/', $repo['url'], $match) && ($match[2] == 'auth')) {
                return $repositories;
            }
        }
        $repositories[] = [
            'type' => 'vcs',
            'url' => 'https://gitlab.smartworld.team:3443/egal-framework/auth.git',
        ];
        return $repositories;
    }

    private function registerModels()
    {
        /** @var ModelManager $modelManager */
        $modelManager = Session::getModelManager();
        foreach (['User', 'Service', 'Role', 'RoleUser', 'RoleService'] as $model) {
            require_once Session::getRegistry()->get('BasePath')
                . '/vendor/egal-framework/auth/src/Metadata/' . $model . '.php';
            require_once Session::getRegistry()->get('BasePath')
                . '/vendor/egal-framework/auth/src/Models/' . $model . '.php';
            $modelManager->register($model, '\\EgalFramework\\Auth\\Models', '\\EgalFramework\\Auth\\Metadata');
            Artisan::call('mk:migration', ['modelName' => $model]);
            sleep(1);
        }
    }

    private function registerCommands()
    {
        $commandManager = Session::getCommandManager();
        $commandManager->register('\\EgalFramework\\Auth\\Commands\\CreateRole');
        $commandManager->register('\\EgalFramework\\Auth\\Commands\\SetGroup');
        $commandManager->register('\\EgalFramework\\Auth\\Commands\\CreateUser');
        $commandManager->register('\\EgalFramework\\Auth\\Commands\\CreateService');

        $this->info(PHP_EOL . 'Don\'t forget to create database, fix and run migrations');
    }

}
