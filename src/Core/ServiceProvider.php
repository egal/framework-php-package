<?php

namespace Egal\Core;

use Egal\Core\Auth\Gate;
use Egal\Core\Http\Route;
use Egal\Core\Rest\Controller;
use Egal\Core\Rest\Filter\Parser as FilterParser;
use Egal\Core\Rest\Select\Parser as SelectParser;
use Egal\Core\Rest\Scope\Parser as ScopeParser;
use Egal\Core\Rest\Order\Parser as OrderParser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{

    public function register()
    {
        $this->app->singleton('egal.gate', fn($app) => new Gate());
        $this->app->singleton('egal.rest', fn($app) => new Controller());
        $this->app->singleton('egal.route', fn($app) => new Route());
        $this->app->singleton('egal.filter.parser', fn($app) => new FilterParser());
        $this->app->singleton('egal.select.parser', fn($app) => new SelectParser());
        $this->app->singleton('egal.scope.parser', fn($app) => new ScopeParser());
        $this->app->singleton('egal.order.parser', fn($app) => new OrderParser());

        if (class_exists('Egal\Core\Console\ServiceProvider')) {
            $this->app->register('Egal\Core\Console\ServiceProvider');
        }

        if (class_exists('Egal\Core\Auth\ServiceProvider')) {
            $this->app->register('Egal\Core\Auth\ServiceProvider');
        }

        if (class_exists('Egal\Core\Auth\RouteServiceProvider')) {
            $this->app->register('Egal\Core\Auth\RouteServiceProvider');
        }

        Collection::macro('paginate', function (int $perPage = 15, string $pageName = 'page', int $page = null, int $total = null, array $options = []): LengthAwarePaginator {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            $results = $this->forPage($page, $perPage)->values();

            $total = $total ?: $this->count();

            $options += [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ];

            return new LengthAwarePaginator($results, $total, $perPage, $page, $options);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }

}
