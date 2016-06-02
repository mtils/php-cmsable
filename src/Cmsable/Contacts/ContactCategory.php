<?php

namespace Cmsable\Contacts;



use Illuminate\Database\Eloquent\Model;

class ContactCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];

    public function contacts()
    {
        return $this->hasMany('Cmsable\Contacts\Contact');
    }
}