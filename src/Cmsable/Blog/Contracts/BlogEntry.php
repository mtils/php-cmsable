<?php


namespace Cmsable\Blog\Contracts;

use Ems\Contracts\Model\HasFrontCover;
use Ems\Contracts\Model\HasContent;
use Ems\Contracts\Model\HasOwner;


interface BlogEntry extends HasFrontCover, HasContent, HasOwner
{


    /**
     * Returns the person which authored (written) this blog entry
     *
     * @return \Ems\Contracts\Core\Identifiable
     **/
    public function getAuthor();

     /**
     * Return the title of this blog post, usually shown above its content
     *
     * @return string
     **/
    public function getTitle();

    /**
     * Return the title which should be shown in breadcrumbs, menu, etc
     *
     * @return string
     **/
    public function getMenuTitle();

    /**
     * Return the topic of this blog post. This is something like Germany in:
     * "Germany - Thousand of potatos fallen on little unicorns"
     *
     * @return string
     **/
    public function getTopic();

    /**
     * Return a url friendly version of its title (or something different)
     *
     * @return string
     **/
    public function getUrlSegment();

    /**
     * Return the preview of its contents (for listing pages). Can be null
     *
     * @return string|null
     **/
    public function getPreviewContent();

    /**
     * Return is this blog post is visible in $context or visible anywhere if
     * no context passed
     *
     * @param string $context (optional)
     * @return string
     **/
    public function isVisible($context=null);

    /**
     * Return a priority of a post to better sort them or show just a distinct
     * priority in a slider
     * 1 is the highest priority, 16 the lowest
     *
     * @return int
     **/
    public function getPriority();

    /**
     * Return a date when the post should be published
     *
     * @return \DateTime
     **/
    public function getPublishAt();

    /**
     * Return the date of this blog entry. This is just for sorting the blog
     * or show its date which is not really the same as a updated_at
     *
     * @return \DateTime
     **/
    public function getDate();

}
