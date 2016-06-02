<?php


namespace Cmsable\Addresses;

use Illuminate\Database\Eloquent\Model;
use Ems\Contracts\Geo\Address as GeoAddress;


class Address extends Model implements GeoAddress
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'addresses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = ['contact_id', 'id'];

    public function contact(){
        return $this->belongsTo('App\Contact');
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function country()
    {
        return 'Deutschland';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function countryCode()
    {
        return 'DEU';
    }

    /**
     * {@inheritdoc}
     * country.
     *
     * @return string
     **/
    public function state()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function county()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function city()
    {
        return $this->location;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function district()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function street()
    {
        return $this->street . ' ' . $this->house_number;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function postCode()
    {
        return $this->postcode;
    }

}
