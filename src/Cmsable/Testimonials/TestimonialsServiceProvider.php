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
        $this->app['events']->listen('form.fields-setted.testimonial-form', function($fields) {
            $this->addTagFieldIfAvailable($fields);
        });
    }

    public function boot()
    {
        $this->registerPageType();
        $this->registerAdminPage();
        $this->registerController();
        $this->registerActions();
        $this->registerTranslations();
        $this->registerCss();
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
        $this->app->alias('testimonials', 'Cmsable\Testimonials\Contracts\TestimonialRepository');
        $this->app->singleton('testimonials', function() {
            $repo = new TestimonialRepository($this->app->make($this->modelInterface));
            $repo->stored(function($model, $attributes){$this->syncTagsWhenStoredOrUpdated($model, $attributes);});
            $repo->updated(function($model, $attributes){$this->syncTagsWhenStoredOrUpdated($model, $attributes);});
            return $repo;
        });
    }

    protected function syncTagsWhenStoredOrUpdated($model, $attributes)
    {
        if (!$this->isTagExtensionEnabled()) {
            return;
        }

        $this->assignTagsByArray($model, $attributes);

        $repo = $this->app->make('Ems\Contracts\Model\Relation\Tag\GlobalTaggingRepository');

        $repo->syncTags($model);

    }

    protected function assignTagsByArray($model, $attributes)
    {

        $model->setTags([]);

        if (!isset($attributes['tags'])) {
            return;
        }

        if (!isset($attributes['tags']['ids'])) {
            return;
        }

        if (!$attributes['tags']['ids']) {
            return;
        }

        $repo = $this->app->make('Ems\Contracts\Model\Relation\Tag\GlobalTaggingRepository');

        foreach ($attributes['tags']['ids'] as $id) {
            if (is_numeric($id)) {
                $model->attachTag($repo->getOrFail($id));
                continue;
            }
            $model->attachTag($repo->getByNameOrCreate($id));
        }
    }

    protected function configureModelPresenter()
    {
        $this->app->resolving('versatile.model-presenter', function($presenter) {

            $presenter->provideKeys($this->testimonialClass(), function($class, $view) {

//                 if ($view == 'preview' || $view == 'fulltext') {
//                     return ['id', 'origin.name'];
//                 }

                return ['id', 'preview_image', 'origin', 'created_at'];

            });
        });


        $this->app['events']->listen('resource::testimonials.query', function($query) {
            $query->leftJoinOn('preview_image');
            $query->addQueryColumn('cite');
        });

    }

    protected function addTagFieldIfAvailable($fields)
    {
        if (!$this->isTagExtensionEnabled()) {
            return;
        }
        $field = $this->app->make('Cmsable\Tags\FormFields\TagField');
        $field->allowNewTags(true);
        $fields->push($field)->after('cite');
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

        $app = $this->app;
        $ns = $this->packageNamespace;

        $this->app['cmsable.breadcrumbs']->register('testimonials.edit', function($breadcrumbs, $id) use ($app, $ns) {
            $key = "$ns::actions.testimonials.edit";
            $breadcrumbs->add($app['translator']->get($key));
        });

        $this->app['cmsable.breadcrumbs']->register('testimonials.create', function($breadcrumbs) use ($app, $ns) {
            $key = "$ns::actions.testimonials.create";
            $breadcrumbs->add($app['translator']->get($key));
        });

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
                                  ->setLangKey($this->packageNamespace.'::pagetypes')
                                  ->setFormPluginClass('Cmsable\Testimonials\SiteTreePlugins\TestimonialsPagePlugin')
                                  ->setControllerCreatorClass('Cmsable\Testimonials\Cms\TestimonialControllerCreator');

            $pageTypes->add($pageType);

        });

        $this->app->afterResolving('Cmsable\PageType\ConfigTypeRepositoryInterface', function($repo){
            $repo->setTemplate('cmsable.testimonials-page', [
                'filter_tags_ids' => []
            ]);
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

    protected function registerCss()
    {

        $events = [
            'cmsable-dist.css.testimonials.edit',
            'cmsable-dist.css.testimonials.create'
        ];

        $this->app['events']->listen($events, function() {
            return '@import url(https://fonts.googleapis.com/css?family=Pacifico);
                    textarea.cite {
                        font-family: Pacifico;
                        font-size: 1.3em;
                        min-height: 200px;
                    }';
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
    
    protected function isTagExtensionEnabled()
    {
        return $this->app->bound('Ems\Contracts\Model\Relation\Tag\GlobalTaggingRepository');
    }

}