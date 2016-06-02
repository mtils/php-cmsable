<?php


namespace Cmsable\Contacts;

use DateTime;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{

        /**
         * The database table used by the model.
         *
         * @var string
         */
        protected $table = 'contact_profiles';

        /**
         * The attributes that are mass assignable.
         *
         * @var array
         */
    protected $guarded = ['contact_id', 'id'];

    public function contact()
    {
        return $this->belongsTo('Cmsable\Contacts\Contact','contact_id');
    }

    public function preview_image()
    {
        return $this->belongsTo('App\File','preview_image_id');
    }

    public function image()
    {
        return $this->belongsTo('App\File','image_id');
    }

    public function avatar()
    {
        return $this->belongsTo('App\File','avatar_id');
    }

}
