<?php namespace Cmsable\Tags\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Ems\Contracts\Model\Relation\Tag\Tag;

class Tags extends Facade
{
    public static function filter($models, $tags)
    {
        $repo = static::getFacadeRoot();
        $repo->attachTags($models);

        $array = $models instanceof Collection ? $models->all() : (array)$models;

        return array_filter($array, function($model) use ($tags){
            return static::compareTags($model, $tags);
        });

    }

    protected static function compareTags($model, $tags) {

        foreach ($tags as $tag) {

            $tagIds = static::tagIds($model->getTags());
            $tagNames = static::tagNames($model->getTags());

            if (is_numeric($tag) && !in_array($tag, $tagIds)) {
                return false;
            }

            if (!is_numeric($tag) && is_string($tag) && !in_array($tag, $tagNames)) {
                return false;
            }

            if ($tag instanceof Tag && !in_array($tag->getId(), $tagIds)) {
                return false;
            }
        }

        return true;

    }

    protected static function tagIds($tags)
    {
        $ids = [];
        foreach ($tags as $tag) {
            $ids[] = $tag->getId();
        }
        return $ids;
    }

    protected static function tagNames($tags)
    {
        $names = [];
        foreach ($tags as $tag) {
            $names[] = $tag->getName();
        }
        return $names;
    }

    protected static function getFacadeAccessor()
    {
        return 'Ems\Contracts\Model\Relation\Tag\GlobalTaggingRepository';
    }

}