<?php

/**
 *  * Created by mtils on 17.11.18 at 09:07.
 **/

namespace Cmsable\Core\Contracts;


use Ems\Contracts\Model\HasContent;
use Ems\Contracts\Tree\Node;

interface Page extends Node, HasContent
{
    /**
     * Mark this page as no redirect.
     */
    const REDIRECT_NONE = 'none';

    /**
     * Mark the page as an internal redirect.
     */
    const REDIRECT_INTERNAL = 'internal';

    /**
     * Mark the page as an external redirect
     */
    const REDIRECT_EXTERNAL = 'external';

    /**
     * Mark this page as a redirect to its first child.
     */
    const REDIRECT_TO_FIRST_CHILD = 'firstchild';

    /**
     * Return the pagetype id.
     *
     * @return string
     */
    public function getPageTypeId();

    /**
     * @return PageType
     */
    public function getType();

    /**
     * Get the type of redirect. Default: self::REDIRECT_NONE
     *
     * @see self::REDIRECT_NONE
     *
     * @return string
     */
    public function getRedirectType();

    /**
     * Get the target of the redirect. On internal redirect the target page id
     * on external an external url.
     *
     * @return string|int
     */
    public function getRedirectTarget();

    /**
     * Get the menu title. This is the title displayed in a navigation.
     *
     * @return string
     */
    public function getMenuTitle();

    /**
     * Get the title of the page. This is typically the big headline on the top
     * of the document and can be used as a tooltip (html title) in links.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Return the locale of this page. (Title/MenuTitle/PathSegment/Content)
     *
     * @return string
     */
    public function getLocale();
}