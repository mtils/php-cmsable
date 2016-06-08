<?php


namespace Cmsable\Tags\Providers;

use Illuminate\Support\ServiceProvider;
use Collection\NestedArray;


class TagServiceProvider extends ServiceProvider
{

    public function register()
    {

        $this->registerTagRepository();

    }
    
    protected function registerTagRepository()
    {
        $interface = 'Ems\Contracts\Model\Relation\Tag\GlobalTaggingRepository';
        $this->app->singleton($interface, function($app){
            return $app->make('Ems\Model\Eloquent\GlobalTaggingRepository', [$app->make($this->tagClass())]);
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