<?php


namespace Cmsable\Blog\Contracts;

use Versatile\Search\Contracts\Search;

interface BlogSearch extends Search
{
    /**
     * Return an array of Ems\Contracts\Core\NamedQuantity objects
     * The count has to be the count of entries in the corresponding year
     * The name and id of the object should be the year
     * The years 
     *
     * @return array
     **/
    public function years();

    /**
     * Return an array of Ems\Contracts\Core\NamedQuantity objects
     * The count has to be the count of entries in the corresponding month
     * The name of the object should be the month (1-12), the id $year-$month
     *
     * @return array
     **/
    public function months($year=null);

    /**
     * Return an array of Ems\Contracts\Core\NamedQuantity objects
     * The count has to be the count of entries in the corresponding month
     * The name of the object should be the month (1-12), the id $year-$month
     *
     * @return array
     **/
    public function days($year=null, $month=null);
}
