<?php

namespace Cmsable\Testimonials\Contracts;


use Ems\Contracts\Core\HasFrontCover;


interface Testimonial extends HasFrontCover
{

    /**
     * Returns the person which originated the Testimonial
     *
     * @return \Ems\Contracts\Named
     **/
    public function getOrigin();

    /**
     * Return the cite of the customer
     *
     * @return string
     **/
    public function getCite();

}