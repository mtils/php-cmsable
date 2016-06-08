<?php 

namespace Cmsable\Testimonials;

use Illuminate\Database\Eloquent\SoftDeletes;
use Cmsable\Testimonials\Contracts\Testimonial as TestimonialContract;
use Ems\Model\Eloquent\Model;
use Ems\Model\Eloquent\FrontCoverByAttribute;
use Ems\Core\NamedObject;
use Ems\Contracts\Model\Relation\Tag\HoldsTags;
use Ems\Model\Relation\Tag\HoldsTagsTrait;
use Ems\Contracts\Core\AppliesToResource;

class FileDBTestimonial extends Model implements TestimonialContract, HoldsTags, AppliesToResource
{

    use SoftDeletes;
    use HoldsTagsTrait;

    public static $originName = 'origin';

    public static $citeName = 'cite';

    public static $fileDbModelKey = 'preview_image_id';

    public static $fileDBModel = 'App\File';

    protected $guarded = ['id'];

    protected $table = 'testimonials';


    /**
     * @inheritdoc
     *
     * @return \Ems\Contracts\Named
     **/
    public function getOrigin()
    {
        $originName = $this->getAttribute(static::$originName);
        return new NamedObject(null, $originName);
    }

    /**
     * @inheritdoc
     *
     * @return string
     **/
    public function getCite()
    {
        return $this->getAttribute(static::$citeName);
    }

    /**
     * @inheritdoc
     *
     * @param int $size (optional)
     * @return string
     **/
    public function getFrontCover($size=0)
    {
        return $this->preview_image->url;
    }

    public function preview_image()
    {
        return $this->belongsTo(static::$fileDBModel);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Ems\Contracts\Core\AppliesToResource
     * @return string
     **/
    public function resourceName()
    {
        return 'testimonials';
    }

}