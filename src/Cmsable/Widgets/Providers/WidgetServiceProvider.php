<?php


namespace Cmsable\Widgets\Providers;

use Cmsable\PageType\PageType;
use Cmsable\Widgets\Contracts\Registry;
use Collection\NestedArray;
use Illuminate\Support\ServiceProvider;
use Cmsable\Widgets\SiteTreePlugins\WidgetAnchorPlugin;

use function realpath;


class WidgetServiceProvider extends ServiceProvider
{

    public $addSampleWidgets = true;

    protected $routeGroup = [
        'namespace' =>  'Cmsable\Widgets\Http\Controllers'
    ];

    protected $packageNamespace = 'widgets';

    protected $packagePath = '';

    public function boot()
    {
        $this->bootRoutes();
        $this->loadTranslationsFrom($this->resourcePath('lang'), $this->packageNamespace);
    }

    public function register()
    {
        $this->registerPageType();
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
            return $app->make($class, ['areaModel' => new $areaClass]);//, $this->app['Cmsable\Widgets\Contracts\WidgetItemRepository']);
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
            return $this->app->make($class, ['model' => new $modelClass]);
        });


    }

    protected function registerSampleWidgets(Registry $registry)
    {
        $registry->set('cmsable.widgets.samples.shout-out-box', 'Cmsable\Widgets\Samples\ShoutOutBoxWidget');
        $registry->set('cmsable.widgets.samples.page-link', 'Cmsable\Widgets\Samples\PageLinkWidget');
    }

    protected function hookIntoPageSaving()
    {
        $this->app['events']->listen('sitetree.*.updated', function($eventName, array $args){
            $form = $args[0];
            $page = $args[1];
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

    protected function registerPageType()
    {
        $this->app['events']->listen('cmsable.pageTypeLoadRequested', function($pageTypes) {

            $pageType = PageType::create('cmsable.widget-anchor')
                ->setCategory('default')
                ->setTargetPath('cms-redirect')
                ->setLangKey($this->packageNamespace.'::pagetypes')
                ->setFormPluginClass(WidgetAnchorPlugin::class);

            $pageTypes->add($pageType);

        });

    }

    protected function resourcePath($dir='')
    {
        $resourcePath = $this->packagePath('resources');

        if ($dir) {
            return "$resourcePath/$dir";
        }

        return $resourcePath;
    }

    protected function packagePath($dir='')
    {
        if (!$this->packagePath) {
            $this->packagePath = realpath(__DIR__.'/..');
        }
        if ($dir) {
            return $this->packagePath . "/$dir";
        }
        return $this->packagePath;
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