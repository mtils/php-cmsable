<?php


namespace Cmsable\Blog\Search;

use Versatile\Query\Builder;
use Cmsable\Blog\BlogEntry;
use Ems\Model\Eloquent\TagQueryHelper;

class BlogQueryBuilder extends Builder
{

    /**
     * @var
     **/
    protected $tagQueryHelper;

    public function __construct(BlogEntry $model)
    {
        parent::__construct($model);

//         $this->with('address', 'picture', 'area', 'area_trade', 'area_living',
//                     'price_buy', 'price_rent', 'price_marketing', 'property_types');

    }

    public function where($column, $operator = null, $value = null, $boolean = 'and'){

        if ($column == 'tag') {
            return $this->tags($value);
        }

        if ($column == 'year') {
            return $this->whereYear($value);
        }

        if ($column == 'month') {
            return $this->whereMonth($value);
        }

        if ($column == 'day') {
            return $this->whereDay($value);
        }

        return parent::where($column, $operator, $value, $boolean);
    }

    /**
     * @return \Ems\Model\Eloquent\TagQueryHelper
     **/
    public function getTagQueryHelper()
    {
        return $this->tagQueryHelper;
    }

    /**
     * Set the tag query helper to support searches by tags
     *
     * @param \Ems\Model\Eloquent\TagQueryHelper $helper
     * @return self
     **/
    public function setTagQueryHelper(TagQueryHelper $helper)
    {
        $this->tagQueryHelper = $helper;
        return $this;
    }

    public function whereYear($year)
    {
        $this->query->where($this->raw('YEAR(blog_date)'), $this->numeric($year));
        return $this;
    }

    public function whereMonth($month)
    {
        $this->query->where($this->raw('MONTH(blog_date)'), $this->numeric($month));
        return $this;
    }

    public function whereDay($day)
    {
        $this->query->where($this->raw('DAY(blog_date)'), $this->numeric($day));
        return $this;
    }

    protected function tags($tagIds)
    {
        if (!$this->tagQueryHelper) {
            return $this;
        }

        $tagIds = (array)$tagIds;

        $this->tagQueryHelper->addTagFilter($this->query->getModel(), $this->query, $tagIds);

        return $this;
    }

    protected function raw($expression)
    {
        return $this->model->getConnection()->raw($expression);
    }

    protected function numeric($number)
    {
        if (!is_numeric($number)) {
            throw new InvalidArgumentException('Number has to be numeric');
        }
        return (int)$number;
    }
}
