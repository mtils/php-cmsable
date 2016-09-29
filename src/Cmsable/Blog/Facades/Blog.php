<?php

namespace Cmsable\Blog\Facades;

use Illuminate\Support\Facades\Facade;
use Cmsable\Blog\Contracts\BlogEntryRepository;



class Blog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BlogEntryRepository::class;
    }
}
