<?php


namespace Cmsable\Blog\Cms;


use Cmsable\Routing\ControllerCreator;
use Illuminate\Routing\Controller;
use Versatile\Search\Contracts\Search;
use Cmsable\Http\Resource\SearchRequest;
use Ems\Model\Eloquent\TagQueryHelper;
// use Versatile\Query\Builder;
use Illuminate\Database\Eloquent\Builder;

class BlogControllerCreator extends ControllerCreator
{

    protected $queryHelper;

    public function __construct(TagQueryHelper $queryHelper)
    {
        $this->queryHelper = $queryHelper;
    }

    public function onIndex(Controller $controller, array $parameters)
    {
        if (!$this->container) {
            return;
        }

        $this->container['events']->listen('resource::blog-entries.query', function($builder) {
            $builder->modifyQuery(function($query){
                $this->modifyBuilder($query);
            });
        });

    }

    public function modifyBuilder(Builder $builder)
    {

        if (!$config = $this->config()) {
            return;
        }

        if (!$tagIds = $config->get('filter_tags_ids')) {
            return;
        }

        $this->queryHelper->addTagFilter($builder->getModel(), $builder, $tagIds);

    }

    protected function getSearchRequest(array $parameters)
    {
        foreach ($parameters as $i=>$parameter) {
            if ($parameter instanceof SearchRequest) {
                return $parameter;
            }
        }
    }
}
