<?php


namespace Cmsable\Blog;

use Cmsable\Blog\Contracts\BlogEntryRepository as BlogEntryRepositoryContract;
use Ems\Model\Eloquent\Repository;
use Illuminate\Database\Eloquent\Model;
use Ems\Model\Eloquent\NotFoundException;
use Ems\Contracts\Core\Identifiable;
use Permit\CurrentUser\ContainerInterface as Auth;
use Ems\Core\GenericTemporalQuantity;
use Ems\Contracts\Core\StringConverter;

class BlogEntryRepository extends Repository implements BlogEntryRepositoryContract
{

    /**
     * @var \Permit\CurrentUser\ContainerInterface
     **/
    protected $auth;

    /**
     * @var \Ems\Contracts\Core\StringConverter
     **/
    protected $stringConverter;

    /**
     * @param \Permit\CurrentUser\ContainerInterface $auth
     * @param \Illuminate\Database\Eloquent\Model $blogEntryModel (optional)
     **/
    public function __construct(Auth $auth, StringConverter $stringConverter, Model $blogEntryModel=null)
    {
        $this->auth = $auth;
        $this->stringConverter = $stringConverter;
        parent::__construct($blogEntryModel ?: new BlogEntry);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $segment
     * @return \Cmsable\Blog\Contracts\BlogEntry
     **/
    public function getByUrlSegment($segment)
    {
        $query = $this->model->newQuery();

        $query->where('url_segment', $segment);

        $this->publish('getting', $query);

        if (!$entry = $query->first()) {
            return;
        }

        $this->publish('got', $entry);

        return $entry;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $segment
     * @return \Cmsable\Blog\Contracts\BlogEntry
     **/
    public function getByUrlSegmentOrFail($segment)
    {

        if ($entry = $this->getByUrlSegment($segment)) {
            return $entry;
        }

        throw new NotFoundException("Blog entry '$segment' not found");
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     **/
    public function years()
    {
        $query = $this->model->newQuery();
        $con = $this->model->getConnection();
        $dateField = 'blog_date';

        $query->select([
                $this->model->getKeyName(),
                'title',
                $con->raw("YEAR($dateField) as year"),
                $con->raw("COUNT(*) as quantity")
            ])
            ->groupBy($con->raw("YEAR($dateField)"));

        $years = [];

        foreach($query->get() as $result) {
            $years[] = (new GenericTemporalQuantity)->setDate($result->year,1,1)
                                        ->setCount($result->quantity);
        }

        return $years;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     **/
    public function months($year=null)
    {
        $query = $this->model->newQuery();
        $con = $this->model->getConnection();
        $dateField = 'blog_date';

        $query->select([
                $this->model->getKeyName(),
                'title',
                $con->raw("YEAR($dateField) as year"),
                $con->raw("MONTH($dateField) as month"),
                $con->raw("COUNT(*) as quantity")
            ])
            ->groupBy($con->raw("YEAR($dateField)"), $con->raw("MONTH($dateField)"));

        if ($year) {
            $query->where($con->raw("YEAR($dateField)"), $year);
        }

        $months = [];

        foreach($query->get() as $result) {
            $months[] = (new GenericTemporalQuantity)
                            ->setDate($result->year, $result->month,1)
                            ->setCount($result->quantity);
        }

        return $months;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     **/
    public function days($year=null, $month=null)
    {
        $query = $this->model->newQuery();
        $con = $this->model->getConnection();
        $dateField = 'blog_date';

        $query->select([
                $this->model->getKeyName(),
                'title',
                $con->raw("YEAR($dateField) as year"),
                $con->raw("MONTH($dateField) as month"),
                $con->raw("DAY($dateField) as day"),
                $con->raw("COUNT(*) as quantity")
            ])
            ->groupBy(
                $con->raw("YEAR($dateField)"),
                $con->raw("MONTH($dateField)"),
                $con->raw("DAY($dateField)")
            );

        if ($year) {
            $query->where($con->raw("YEAR($dateField)"), $year);
        }

        if ($month) {
            $query->where($con->raw("MONTH($dateField)"), $month);
        }

        $days = [];

        foreach($query->get() as $result) {
            $days[] = (new GenericTemporalQuantity)
                            ->setDate($result->year, $result->month, $result->day)
                            ->setCount($result->quantity);
        }

        return $days;

    }

    /**
     * {@inheritdoc}
     *
     * @param \Ems\Contracts\Core\Identifiable $model
     * @param array $attributes
     * @return bool if attributes where changed after filling
     **/
    public function fill(Identifiable $model, array $attributes)
    {

        if (!$model->exists) {
            $model->owner_id = $this->auth->user()->getAuthId();
        }

        $result = parent::fill($model, $attributes);

        if (!$model->url_segment && $model->title) {
            $model->url_segment = $this->stringConverter->convert($model->title, 'URL-SEGMENT');
        }

        return $result;

    }

}
