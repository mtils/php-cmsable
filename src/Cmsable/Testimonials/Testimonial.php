<?php 

namespace Cmsable\Testimonials;

use Illuminate\Database\Eloquent\SoftDeletes;
use Cmsable\Testimonials\Contracts\Testimonial as TestimonialContract;
use Ems\Model\Eloquent\Model;
use Ems\Model\Eloquent\FrontCoverByAttribute;
use Ems\Core\NamedObject;

class Testimonial extends Model implements TestimonialContract
{

    use SoftDeletes;
    use FrontCoverByAttribute;

    protected $originName = 'origin';

    protected $citeName = 'cite';

    protected $guarded = ['id'];

    /**
     * @inheritdoc
     *
     * @return \Ems\Contracts\Named
     **/
    public function getOrigin()
    {
        $originName = $this->getAttribute($this->originName);
        return new NamedObject(null, $originName);
    }

    /**
     * @inheritdoc
     *
     * @return string
     **/
    public function getCite()
    {
        return $this->getAttribute($this->citeName);
    }

}