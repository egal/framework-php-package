<?php

use App\Menu;
use EgalFramework\APIContainer\Parser\API as APIParser;
use EgalFramework\APIContainer\Storage\Redis as APIStorage;
use EgalFramework\CommandLine\CommandManager;
use EgalFramework\CommandLine\ModelManager;
use EgalFramework\CommandLine\QueuePoolManager;
use EgalFramework\Common\Session;
use EgalFramework\Common\Settings;
use EgalFramework\FilterQuery\FilterQuery;
use EgalFramework\Kerberos\API as KerberosAPI;
use EgalFramework\RedisQueue\API;
use EgalFramework\Request\Send;
use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use EgalFramework\Common\Registry;
use Laravel\Lumen\Application;

/** @var Application $app */

$registry = new Registry();
$registry->set('AppPath', $app->path());
$registry->set('BasePath', $app->basePath());
$registry->set('DBPath', $app->databasePath());
$registry->set('SeedPath', $app->databasePath('seeds'));
$registry->set('FactoryPath', $app->databasePath('factories'));
Session::setRegistry($registry);

Session::setQueue(new API(app('redis'), env('QUEUE_HASH_SALT'), env('APP_NAME')));
Session::setApiStorage(new APIStorage(app('redis'), 'services:' . env('APP_NAME')));
Session::setMenu(new Menu);
Session::setModelManager(new ModelManager);
Session::setCommandManager(new CommandManager);
Session::setFilterQuery(new FilterQuery);
Session::setValidateCallback(function (array $attributes, array $rules) {
    unset($rules['hash']);
    $validator = Validator::make($attributes, $rules);
    if (!$validator->passes()) {
        return $validator->errors()->all();
    }
    return [];
});
Session::setApiParser(new APIParser);
Session::setKerberosApi(new KerberosAPI);
if (class_exists(Send::class)) {
    Session::setSendRequest(new Send(env('APP_NAME'), env('APP_KEY')));
}
/** @var RedisStore $requestCache */
$requestCache = Cache::store('redis')->getStore();
$requestCache->setPrefix('entity_cache_' . env('APP_NAME'));
Session::setRequestCache($requestCache);

Settings::setAppName(env('APP_NAME'));
Settings::setAppKey(env('APP_KEY'));
Settings::setDebugMode(env('APP_DEBUG', false));
Settings::setDisableAuth(env('DISABLE_AUTH', false));
Settings::setDisableCache(env('DISABLE_REDIS_CACHE', false));
Settings::setDefaultMaxRelations(env('DEFAULT_MAX_RELATIONS', Settings::DEFAULT_MAX_RELATIONS));
$composerFile = json_decode(file_get_contents($registry->get('BasePath') . '/composer.json'), true);
Settings::setIsAuth(isset($composerFile['require']['egal-framework/auth']));
Settings::setHostAddress(trim(shell_exec('route | grep default | awk \'{print $2}\'')));
