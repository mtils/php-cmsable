<?php 

namespace Cmsable\Testimonials;

use Illuminate\Database\Eloquent\SoftDeletes;
use Cmsable\Testimonials\Contracts\Testimonial as TestimonialContract;
use Ems\Model\Eloquent\Model;
use Ems\Model\Eloquent\FrontCoverByAttribute;
use Ems\Core\NamedObject;

class FileDBTestimonial extends Model implements TestimonialContract
{

    use SoftDeletes;

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

}