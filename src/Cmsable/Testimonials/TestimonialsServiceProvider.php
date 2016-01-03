<?php 

namespace Cmsable\Testimonials;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Ems\Model\Eloquent\Repository;

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
        $this->registerController();
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
    }

    protected function registerController()
    {
        $this->app->bind($this->controllerClass, function() {

            $repository = $this->app->make('testimonials');
            $notifier = $this->app->make('Cmsable\View\Contracts\Notifier');

            $controllerClass = $this->controllerClass;

            $controller = new $controllerClass($repository, $notifier);

            return $controller;

        });

        $this->app->router->resource('testimonials', $this->controllerClass);
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