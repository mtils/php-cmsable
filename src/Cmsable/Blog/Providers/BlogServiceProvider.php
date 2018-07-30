<?php


namespace Cmsable\Blog\Providers;

use Illuminate\Support\ServiceProvider;
use Collection\NestedArray;
use Ems\Model\Eloquent\TagQueryHelper;
use Cmsable\Blog\Contracts\BlogEntry as BlogEntryContract;
use Cmsable\Blog\BlogEntry;
use Cmsable\Blog\Contracts\BlogEntryRepository as BlogEntryRepositoryContract;
use Cmsable\Blog\BlogEntryRepository;
use Illuminate\Contracts\Routing\Registrar;
use Cmsable\Blog\Http\Controllers\BlogEntryController;
use Cmsable\Blog\Http\Forms\BlogEntryForm;
use Cmsable\Cms\Action\Action;
use Cmsable\PageType\PageType;


class BlogServiceProvider extends ServiceProvider
{

    protected $packagePath = '';
    protected $packageNamespace = 'cmsable-blog';

    public function register()
    {

        $this->registerModel();
        $this->registerRepository();
        $this->configureModelPresenter();

        $this->app['events']->listen('form.fields-setted.blog-entry-form', function($fields) {
            $this->addTagFieldIfAvailable($fields);
        });

        $this->app->resolving('versatile.search-factory', function($factory) {
            $this->registerBlogQueryBuilder($factory);
        });


    }

    public function boot()
    {
        $this->registerPageType();
        $this->registerAdminPage();
        $this->registerRoutes($this->app->make(Registrar::class));
        $this->registerActions();
        $this->registerTranslations();
    }

    protected function registerModel()
    {
        $this->app->bind(BlogEntryContract::class, BlogEntry::class);

        $this->app->afterResolving('cmsable.resource-mapper', function($mapper) {
            $mapper->mapModelClass('blog-entries', BlogEntry::class);
            $mapper->mapModelClass('blog-entries.archive', BlogEntry::class);
            $mapper->mapModelClass('blog-entries.archive.', BlogEntry::class);
            $mapper->mapFormClass('blog-entries', BlogEntryForm::class);
        });
    }

    protected function configureModelPresenter()
    {
        $this->app->resolving('versatile.model-presenter', function($presenter) {

            $presenter->provideKeys(BlogEntry::class, function($class, $view) {

//                 if ($view == 'preview' || $view == 'fulltext') {
//                     return ['id', 'origin.name'];
//                 }

                return ['id', 'preview_image', 'title', 'topic', 'blog_date'];

            });
        });


        $this->app['events']->listen('resource::blog-entries.query', function($query) {
            $query->leftJoinOn('preview_image');
            $query->addQueryColumn('preview_content');
            $query->addQueryColumn('content');
            $query->addQueryColumn('topic');
            $query->addQueryColumn('url_segment');
        });

    }

    protected function registerRepository()
    {
        $this->app->singleton(BlogEntryRepositoryContract::class, function ($app) {
            $repo = $app->make(BlogEntryRepository::class);
            $repo->stored(function($model, $attributes){$this->syncTagsWhenStoredOrUpdated($model, $attributes);});
            $repo->updated(function($model, $attributes){$this->syncTagsWhenStoredOrUpdated($model, $attributes);});
            return $repo;
        });
    }

    protected function registerBlogQueryBuilder($factory)
    {
        $factory->forModelClass(BlogEntry::class, function($criteria) {

            $modelClass = $criteria->modelClass();

            $builder = $this->app->make('Cmsable\Blog\Search\BlogQueryBuilder', ['model' => new $modelClass]);

            if ($this->isTagExtensionEnabled()) {
                $builder->setTagQueryHelper($this->app->make('Ems\Model\Eloquent\TagQueryHelper'));
            }

            $search = $this->app->make('Versatile\Search\BuilderSearch',[
                'builder' => $builder,
                'criteria' => $criteria
            ]);

            $search->onBuilding(function($builder) {
                $this->app['events']->fire("resource::blog-entries.query", $builder);
            });
            return $search;
        });
    }

    protected function registerRoutes(Registrar $router)
    {
        $router->resource('blog-entries', BlogEntryController::class);

        $router->get('blog-entries/archive/{year}', [
            'as' => 'blog-entries.archive.year',
            'uses' => BlogEntryController::class . '@byYear'
        ]);

        $router->get('blog-entries/archive/{year}/{month}', [
            'as' => 'blog-entries.archive.month',
            'uses' => BlogEntryController::class . '@byMonth'
        ]);

        $router->get('blog-entries/archive/{year}/{month}/{day}', [
            'as' => 'blog-entries.archive.day',
            'uses' => BlogEntryController::class . '@byDay'
        ]);

        $app = $this->app;
        $ns = $this->packageNamespace;

        $this->app['cmsable.breadcrumbs']->register('blog-entries.edit', function($breadcrumbs, $id) use ($app, $ns) {
            $key = "$ns::actions.blog_entry.edit";
            $breadcrumbs->add($app['translator']->get($key));
        });

        $this->app['cmsable.breadcrumbs']->register('blog-entries.create', function($breadcrumbs) use ($app, $ns) {
            $key = "$ns::actions.blog_entry.create";
            $breadcrumbs->add($app['translator']->get($key));
        });

        $this->app['cmsable.breadcrumbs']->register('blog-entries.show', function($breadcrumbs, $id) use ($app, $ns) {
            $repo = $app[BlogEntryRepositoryContract::class];
            $entry = is_numeric($id) ? $repo->get($id) : $repo->getByUrlSegment($id);
            $breadcrumbs->add($entry->getTitle());
        });

        $this->app['cmsable.breadcrumbs']->register('blog-entries.archive.year', function($breadcrumbs, $year) use ($app, $ns) {
            $link = $app['url']->route('blog-entries.archive.year', [$year]);
            $breadcrumbs->add($year, $link);
        });

        $this->app['cmsable.breadcrumbs']->register('blog-entries.archive.month', function($breadcrumbs, $year, $month) use ($app, $ns) {

            $yearLink = $app['url']->route('blog-entries.archive.year', [$year]);
            $monthLink = $app['url']->route('blog-entries.archive.month', [$year, $month]);

            $breadcrumbs->add($year, $yearLink);
            $breadcrumbs->add($month, $monthLink);

        });

        $this->app['cmsable.breadcrumbs']->register('blog-entries.archive.day', function($breadcrumbs, $year, $month, $day) use ($app, $ns) {

            $yearLink = $app['url']->route('blog-entries.archive.year', [$year]);
            $monthLink = $app['url']->route('blog-entries.archive.month', [$year, $month]);
            $dayLink = $app['url']->route('blog-entries.archive.day', [$year, $month, $day]);

            $breadcrumbs->add($year, $yearLink);
            $breadcrumbs->add($month, $monthLink);
            $breadcrumbs->add($day, $dayLink);

        });

    }

    protected function registerActions()
    {

        $this->app['cmsable.actions']->onType(BlogEntry::class, function($group, $user, $context) {

            if (!$this->app['auth']->allowed('cms.access')){
                return;
            }

            $url = $this->app['url']->route('blog-entries.create');
            $create = new Action();
            $create->setName('blog-entries.create')->setTitle(
                $this->app['translator']->get($this->packageNamespace.'::actions.blog_entry.create')
            );
            $create->setUrl($url);
            $create->setIcon('fa-search');
            $create->showIn('blog-entries','main');
            $group->push($create);

        });

        $this->app['cmsable.actions']->onItem(BlogEntry::class, function($group, $user, $resource) {

            if (!$this->app['auth']->allowed('cms.access')){
                return;
            }

            $url = $this->app['url']->route('blog-entries.edit', [$resource->getKey()]);
            $edit = new Action();
            $edit->setName('testimonials.edit')->setTitle(
                $this->app['translator']->get($this->packageNamespace.'::actions.blog_entry.edit')
            );
            $edit->setUrl($url);
            $edit->setIcon('fa-edit');
            $edit->showIn('blog-entries');
            $group->push($edit);

        });

        $this->app['cmsable.actions']->onItem(BlogEntry::class, function($group, $user, $resource) {

            if (!$this->app['auth']->allowed('cms.access')){
                return;
            }

            $url = $this->app['url']->route('blog-entries.destroy', [$resource->getKey()]);
            $destroy = new Action();
            $destroy->setName('blog-entries.destroy')->setTitle(
                $this->app['translator']->get($this->packageNamespace.'::actions.blog_entry.destroy')
            );
            $destroy->setIcon('fa-trash');
            $destroy->showIn('blog-entries');
            $destroy->setOnClick("deleteResource('$url', this); return false;");
            $destroy->setData('confirm-message', $this->app['translator']->get($this->packageNamespace.'::actions.blog_entry.destroy-confirm'));
            $group->push($destroy);

        });

    }

    protected function registerTranslations()
    {

        $this->loadTranslationsFrom($this->resourcePath('lang'), $this->packageNamespace);

        $this->app->resolving('Versatile\Introspection\Contracts\TitleIntrospector', function($introspector) {
            $introspector->mapModelToLangName(BlogEntry::class, 'blog_entry');
            $introspector->setLangNamespace('blog_entry', $this->packageNamespace);
        });
    }

    protected function registerPageType()
    {
        $this->app['events']->listen('cmsable.pageTypeLoadRequested', function($pageTypes) {

            $pageType = PageType::create('cmsable.blog-page')
                                  ->setCategory('default')
                                  ->setTargetPath('blog-entries')
                                  ->setLangKey($this->packageNamespace.'::pagetypes')
                                  ->setFormPluginClass('Cmsable\Blog\SiteTreePlugins\BlogPagePlugin')
                                  ->setControllerCreatorClass('Cmsable\Blog\Cms\BlogControllerCreator')
;

            $pageTypes->add($pageType);

        });

        $this->app->afterResolving('Cmsable\PageType\ConfigTypeRepositoryInterface', function($repo){
            $repo->setTemplate('cmsable.blog-page', [
                'filter_tags_ids' => []
            ]);
        });
    }

    protected function registerAdminPage()
    {
        $this->app['events']->listen('sitetree.filled', function(&$adminTreeArray){

            $testimonialPage = [
                'id'                => 'blog',
                'page_type'         => 'cmsable.blog-page',
                'url_segment'       => 'blog',
                'icon'              => 'fa-bullhorn',
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

    protected function addTagFieldIfAvailable($fields)
    {
        if (!$this->isTagExtensionEnabled()) {
            return;
        }
        $field = $this->app->make('Cmsable\Tags\FormFields\TagField');
        $field->allowNewTags(true);
        $fields('main')->push($field)->after('content');
    }

    protected function isTagExtensionEnabled()
    {
        return $this->app->bound('Ems\Contracts\Model\Relation\Tag\GlobalTaggingRepository');
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

}
