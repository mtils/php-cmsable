<?php 

namespace Cmsable\Testimonials;

use ReflectionProperty;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Ems\Model\Eloquent\Repository;
use Cmsable\Cms\Action\Action;
use Cmsable\PageType\PageType;

class TestimonialsServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    protected $modelInterface = 'Cmsable\Testimonials\Contracts\Testimonial';

    protected $controllerClass = 'Cmsable\Testimonials\Http\Controllers\TestimonialController';

    protected $formClass = 'Cmsable\Testimonials\Http\Forms\TestimonialForm';

    protected $packagePath = '';

    protected $packageNamespace = 'testimonials';

    public function register()
    {
        $this->registerModel();
        $this->registerRepository();
        $this->configureModelPresenter();
    }

    public function boot()
    {
        $this->registerPageType();
        $this->registerAdminPage();
        $this->registerController();
        $this->registerActions();
        $this->registerTranslations();
    }

    protected function registerModel()
    {
        $this->app->bind($this->modelInterface, function(){
            return $this->app->make($this->testimonialClass());
        });

        $this->app->afterResolving('cmsable.resource-mapper', function($mapper) {
            $mapper->mapModelClass('testimonials', $this->testimonialClass());
        });

        $this->app->afterResolving('cmsable.resource-mapper', function($mapper) {
            $mapper->mapFormClass('testimonials', $this->formClass);
        });

        $this->app['events']->listen('resource::testimonials.input.casted', function(&$input){

            // This will only be done once and will this way executed later
            // So no need to put it outside the closure
            if (!property_exists($this->testimonialClass(), 'fileDbModelKey')) {
                return;
            }

            $fileDbModelProperty = new ReflectionProperty($this->testimonialClass(), 'fileDbModelKey');

            $fileDbModelKey = $fileDbModelProperty->getValue();

            if (!isset($input[$fileDbModelKey])) {
                return;
            }

            // If a not trueish value of this foreign key is set, set it to null
            if (!$input[$fileDbModelKey]) {
                $input[$fileDbModelKey] = null;
            }

        });
    }

    protected function registerRepository()
    {
        $this->app->singleton('testimonials', function() {
            return new Repository($this->app->make($this->modelInterface));
        });
    }

    protected function configureModelPresenter()
    {
        $this->app->resolving('versatile.model-presenter', function($presenter) {

            $presenter->provideKeys($this->testimonialClass(), function($class, $view) {

//                 if ($view == 'preview' || $view == 'fulltext') {
//                     return ['id', 'origin.name'];
//                 }

                return ['id', 'origin', 'preview_image.url', 'created_at'];

            });
        });


        $this->app['events']->listen('resource::testimonials.query', function($query) {
            $query->leftJoinOn('preview_image');
        });

    }

    protected function registerController()
    {
        $this->app->bind($this->controllerClass, function() {

            $repository = $this->app->make('testimonials');
            $notifier = $this->app->make('Cmsable\View\Contracts\Notifier');

            $controllerClass = $this->controllerClass;

            $controller = new $controllerClass($repository, $notifier);

            $controller->setTranslationNamespace($this->packageNamespace);

            return $controller;

        });

        $this->app->router->resource('testimonials', $this->controllerClass);

    }

    protected function registerActions()
    {

        $this->app['cmsable.actions']->onType($this->testimonialClass(), function($group, $user, $context) {

            if (!$this->app['auth']->allowed('cms.access')){
                return;
            }

            $url = $this->app['url']->route('testimonials.create');
            $create = new Action();
            $create->setName('testimonials.create')->setTitle(
                $this->app['translator']->get($this->packageNamespace.'::actions.testimonials.create')
            );
            $create->setUrl($url);
            $create->setIcon('fa-search');
            $create->showIn('testimonials','main');
            $group->push($create);

        });

        $this->app['cmsable.actions']->onItem($this->testimonialClass(), function($group, $user, $resource) {

            if (!$this->app['auth']->allowed('cms.access')){
                return;
            }

            $url = $this->app['url']->route('testimonials.edit', [$resource->getKey()]);
            $edit = new Action();
            $edit->setName('testimonials.edit')->setTitle(
                $this->app['translator']->get($this->packageNamespace.'::actions.testimonials.edit')
            );
            $edit->setUrl($url);
            $edit->setIcon('fa-edit');
            $edit->showIn('testimonials');
            $group->push($edit);

        });

        $this->app['cmsable.actions']->onItem($this->testimonialClass(), function($group, $user, $resource) {

            if (!$this->app['auth']->allowed('cms.access')){
                return;
            }

            $url = $this->app['url']->route('testimonials.destroy', [$resource->getKey()]);
            $destroy = new Action();
            $destroy->setName('testimonials.destroy')->setTitle(
                $this->app['translator']->get($this->packageNamespace.'::actions.testimonials.destroy')
            );
            $destroy->setIcon('fa-trash');
            $destroy->showIn('testimonials');
            $destroy->setOnClick("deleteResource('$url', this); return false;");
            $destroy->setData('confirm-message', $this->app['translator']->get($this->packageNamespace.'::actions.testimonials.destroy-confirm'));
            $group->push($destroy);

        });

    }

    protected function testimonialClass()
    {
        if ($this->app->bound('filedb.model')) {
            return 'Cmsable\Testimonials\FileDBTestimonial';
        }
        return 'Cmsable\Testimonials\Testimonial';
    }

    protected function registerTranslations()
    {

        $this->loadTranslationsFrom($this->resourcePath('lang'), $this->packageNamespace);

        $this->app->resolving('Versatile\Introspection\Contracts\TitleIntrospector', function($introspector) {
            $introspector->mapModelToLangName($this->testimonialClass(), 'testimonial');
            $introspector->setLangNamespace('testimonial', $this->packageNamespace);
        });
    }

    protected function registerPageType()
    {
        $this->app['events']->listen('cmsable.pageTypeLoadRequested', function($pageTypes) {

            $pageType = PageType::create('cmsable.testimonials-page')
                                  ->setCategory('default')
                                  ->setTargetPath('testimonials')
                                  ->setLangKey($this->packageNamespace.'::pagetypes');

            $pageTypes->add($pageType);


        });
    }

    protected function registerAdminPage()
    {
        $this->app['events']->listen('sitetree.filled', function(&$adminTreeArray){

            $testimonialPage = [
                'id'                => 'testimonials',
                'page_type'         => 'cmsable.testimonials-page',
                'url_segment'       => 'testimonials',
                'icon'              => 'fa-quote-left',
                'title'             => $this->app['translator']->get($this->packageNamespace.'::admintree.testimonials.title'),
                'menu_title'        => $this->app['translator']->get($this->packageNamespace.'::admintree.testimonials.menu_title'),
                'show_in_menu'      => true,
                'show_in_aside_menu'=> false,
                'show_in_search'    => true,
                'show_when_authorized' => true,
                'redirect_type'     => 'none',
                'redirect_target'   => 0,
                'content'           => $this->app['translator']->get($this->packageNamespace.'::admintree.testimonials.content'),
                'view_permission'   => 'cms.access',
                'edit_permission'   => 'cms.access'
            ];

            foreach ($adminTreeArray['children'] as $i=>$child) {
                if ($child['id'] == 'sitetree-parent') {
                    $adminTreeArray['children'][$i]['children'][] = $testimonialPage;
                    break;
                }
            }

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
            $this->packagePath = realpath(__DIR__);
        }
        if ($dir) {
            return $this->packagePath . "/$dir";
        }
        return $this->packagePath;
    }

}