<?php


namespace Cmsable\Contacts;

use DateTime;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{

    /**
    * The database table used by the model.
    *
    * @var string
    */
    protected $table = 'contacts';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $guarded = ['user_id', 'id'];

    public function address(){
        return $this->hasOne('Cmsable\Addresses\Address');
    }

    public function user(){
        return $this->belongsTo('App\User','user_id');
    }

    public function category()
    {
        return $this->belongsTo('Cmsable\Contacts\ContactCategory');
    }

    public function profile()
    {
        return $this->hasOne('Cmsable\Contacts\Profile');
    }


    public function getSummaryAttribute()
    {
        return $this->forename . ' ' . $this->surname;
    }

    public static function byEmail($email)
    {
        return static::where('users.email', $email)
                       ->select('contacts.*','users.email')
                       ->join('users','users.id','=','contacts.user_id');
    }

}
