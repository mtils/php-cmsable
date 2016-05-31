<?php


namespace Cmsable\Widgets\Providers;

use Illuminate\Support\ServiceProvider;
use Cmsable\Widgets\Contracts\AreaRepository as AreaRepositoryContract;
use Cmsable\Widgets\Contracts\Registry;
use Collection\NestedArray;


class WidgetServiceProvider extends ServiceProvider
{

    public $addSampleWidgets = true;

    protected $routeGroup = [
        'namespace' =>  'Cmsable\Widgets\Http\Controllers'
    ];

    public function boot()
    {
        $this->bootRoutes();
    }

    public function register()
    {
        $this->registerWidgetRegistry();
        $this->hookIntoPageSaving();
        $this->registerAreaRepository();
        $this->registerAreaRenderer();
        $this->registerWidgetItemRepository();

        if (!$this->addSampleWidgets) {
            return;
        }

        $this->app->afterResolving('Cmsable\Widgets\Contracts\Registry', function($reg, $app){
            $this->registerSampleWidgets($reg);
        });

    }

    protected function registerAreaRepository()
    {

        $class = $this->areaRepositoryClass();
        $areaClass = $this->areaClass();
        $interface = 'Cmsable\Widgets\Contracts\AreaRepository';

        $this->app->singleton($interface, function($app) use ($class, $areaClass) {
            return new $class(new $areaClass, $this->app['Cmsable\Widgets\Contracts\WidgetItemRepository']);
        });

    }

    protected function registerAreaRenderer()
    {
        $interface = 'Cmsable\Widgets\Contracts\AreaRenderer';
        $class = $this->areaRendererClass();
        $this->app->singleton($interface, function($app) use ($class) {
            return $app->make($class);
        });
    }

    protected function registerWidgetRegistry()
    {
        $interface = 'Cmsable\Widgets\Contracts\Registry';
        $class = 'Cmsable\Widgets\Registry';
        $alias = 'cmsable.widgets.registry';
        $this->app->alias($interface, $alias);
        $this->app->singleton($interface, function($app) use ($class) {
            $registry = $app->make($class);
            $registry->createWidgetsWith(function($class){
                return $this->app->make($class);
            });
            return $registry;
        });
    }

    protected function registerWidgetItemRepository()
    {
        $interface = 'Cmsable\Widgets\Contracts\WidgetItemRepository';
        $class = $this->widgetItemRepositoryClass();
        $modelClass = $this->widgetItemClass();

        $this->app->alias('cmsable.widgets.items', $interface);

        $this->app->singleton($interface, function($app) use ($class, $modelClass){
            return $this->app->make($class, [new $modelClass]);
        });


    }

    protected function registerSampleWidgets(Registry $registry)
    {
        $registry->set('cmsable.widgets.samples.shout-out-box', 'Cmsable\Widgets\Samples\ShoutOutBoxWidget');
    }

    protected function hookIntoPageSaving()
    {
        $this->app['events']->listen('sitetree.*.updated', function($form, $page){

            $data = NestedArray::toNested($this->app['request']->all(),'__');
            $areas = $this->app['Cmsable\Widgets\Contracts\AreaRepository'];

            if (!isset($data['widgets'])) {
                return;
            }

            foreach ($data['widgets'] as $areaName=>$areaData) {
                $area = $areas->areaFor($page->getPagetypeId(), $page->getIdentifier(), $areaName);
                $areas->update($area, $areaData);
            }

        });
    }

    protected function bootRoutes()
    {
        $this->app->router->group($this->routeGroup, function($router){

            $router->get('widgets/{widgets}/items/{items}/edit',[
                'as'   => 'widgets.items.edit',
                'uses' => 'WidgetController@editItem'
            ]);

            $router->post('widgets/{widgets}/items/{items}/edit-preview',[
                'as'   => 'widgets.items.edit-preview',
                'uses' => 'WidgetController@editPreview'
            ]);

            $router->get('widgets/{widgets}/items/create',[
                'as'   => 'widgets.items.create',
                'uses' => 'WidgetController@createItem'
            ]);

            $router->get('widgets/{widgets}',[
                'as'   => 'widgets.show',
                'uses' => 'WidgetController@show'
            ]);

            $router->post('widgets/{widgets}/items/show-if-valid',[
                'as'   => 'widgets.items.show-if-valid',
                'uses' => 'WidgetController@showIfValid'
            ]);

            $router->get('widgets',[
                'as'   => 'widgets.index',
                'uses' => 'WidgetController@index'
            ]);

        });
    }

    protected function areaRepositoryClass()
    {
        return 'Cmsable\Widgets\Repositories\AreaRepository';
    }

    protected function areaClass()
    {
        return 'Cmsable\Widgets\Area';
    }

    protected function areaRendererClass()
    {
        return 'Cmsable\Widgets\ViewAreaRenderer';
    }

    protected function widgetItemClass()
    {
        return 'Cmsable\Widgets\WidgetItem';
    }

    protected function widgetItemRepositoryClass()
    {
        return 'Cmsable\Widgets\Repositories\WidgetItemRepository';
    }

}