<?php


namespace Cmsable\Blog\Contracts;

use Ems\Contracts\Model\ExtendableRepository;



interface BlogEntryRepository extends ExtendableRepository
{

    /**
     * Return a blog entry be its url segment
     *
     * @param string $segment
     * @return \Cmsable\Blog\Contracts\BlogEntry
     **/
    public function getByUrlSegment($segment);

    /**
     * Return a blog entry be its url segment or throw a notfound
     *
     * @param string $segment
     * @return \Cmsable\Blog\Contracts\BlogEntry
     **/
    public function getByUrlSegmentOrFail($segment);

    /**
     * Return an array of Ems\Contracts\Core\TemporalQuantity objects
     * The count has to be the count of entries in the corresponding year
     *
     * @return array
     **/
    public function years();

    /**
     * Return an array of Ems\Contracts\Core\TemporalQuantity objects
     * The count has to be the count of entries in the corresponding month
     *
     * @return array
     **/
    public function months($year=null);

    /**
     * Return an array of Ems\Contracts\Core\TemporalQuantity objects
     * The count has to be the count of entries on the corresponding day
     *
     * @return array
     **/
    public function days($year=null, $month=null);
}
