<?php


namespace Cmsable\Tags\Providers;

use Illuminate\Support\ServiceProvider;
use Collection\NestedArray;
use Ems\Model\Eloquent\TagQueryHelper;


class TagServiceProvider extends ServiceProvider
{

    public function register()
    {

        $this->registerTagRepository();
        $this->registerTagQueryHelper();

    }

    protected function registerTagRepository()
    {
        $interface = 'Ems\Contracts\Model\Relation\Tag\GlobalTaggingRepository';
        $this->app->singleton($interface, function($app){
            return $app->make('Ems\Model\Eloquent\GlobalTaggingRepository', [$app->make($this->tagClass())]);
        });
    }

    protected function registerTagQueryHelper()
    {
        $this->app->bind('Ems\Model\Eloquent\TagQueryHelper', function($app) {
            $helper = new TagQueryHelper($app->make($this->tagClass()));
            
            return $helper;
        });
    }

    public function boot()
    {
    }
    
    protected function tagClass()
    {
        return '\Ems\Model\Eloquent\Tag';
    }
}