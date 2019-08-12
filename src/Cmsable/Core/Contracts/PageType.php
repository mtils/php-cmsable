<?php

namespace Cmsable\Core\Contracts;

use Ems\Contracts\Core\AssignedToLocale;
use Ems\Core\NamedObject;

/**
 *  * Created by mtils on 25.11.18 at 08:51.
 **/

class PageType extends NamedObject implements AssignedToLocale
{
    /**
     * @var string
     */
    protected $targetPath;

    /**
     * @var string
     */
    protected $locale = 'en_US';

    /**
     * @var string
     */
    protected $cmsIcon;

    /**
     * @var string
     */
    protected $pluralName = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var PageTypeCategory
     */
    protected $category;

    /**
     * @var string[]
     */
    protected $allowedChildrenTypeIds = ['*'];

    /**
     * @var string[]
     */
    protected $allowedParentTypeIds = ['*'];

    /**
     * @var bool
     */
    protected $canBeRoot = true;

    /**
     * @var string
     */
    protected $routeScope = 'default';

    /**
     * @var string
     */
    protected $actionConfiguratorClass = '';

    /**
     * @var array
     */
    protected $routeNamesForReverseMatch = [];

    /**
     * Return the route path of this page type. Which application
     * route should handle a page of this type? Add its path here.
     *
     * @return string
     */
    public function getTargetPath()
    {
        return $this->targetPath;
    }

    /**
     * @see self::getTargetPath()
     *
     * @param string $targetPath
     *
     * @return $this
     */
    public function setTargetPath($targetPath)
    {
        $this->targetPath = $targetPath;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @see AssignedToLocale
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Return a icon name/url to display this type
     *
     * @return string
     */
    public function getCmsIcon()
    {
        return $this->cmsIcon;
    }

    /**
     * @param string $cmsIcon
     *
     * @return $this
     */
    public function setCmsIcon($cmsIcon)
    {
        $this->cmsIcon = $cmsIcon;
        return $this;
    }

    /**
     * Get the name when used in many pages.
     *
     * @return string
     */
    public function getPluralName()
    {
        return $this->pluralName;
    }

    /**
     * @see self::getPluralName()
     *
     * @param string $pluralName
     *
     * @return PageType
     */
    public function setPluralName($pluralName)
    {
        $this->pluralName = $pluralName;
        return $this;
    }

    /**
     * Get a (more verbose) description for this PageType.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @see self::getDescription()
     *
     * @param string $description
     *
     * @return PageType
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Return a category this page-type belongs to. This is usefull to group
     * the page-types in the cms
     *
     * @return PageTypeCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @see self::getCategory()
     *
     * @param PageTypeCategory $category
     *
     * @return $this
     */
    public function setCategory(PageTypeCategory $category)
    {
        $this->category = $category;
        return $this;
    }


    /**
     * Pages with this type id allow only pages with the returned list of page
     * type ids to be added as children of this page.
     *
     * If you return ['*'] generally all are allowed (Default). Return a list
     * of page type ids to restrict it to that page type ids.
     *
     * @return string[]
     */
    public function getAllowedChildrenTypeIds()
    {
        return $this->allowedChildrenTypeIds;
    }

    /**
     * @see self::getAllowedChildrenTypeIds()
     *
     * @param string[] $allowedChildrenTypeIds
     *
     * @return $this
     */
    public function setAllowedChildrenTypeIds(array $allowedChildrenTypeIds)
    {
        $this->allowedChildrenTypeIds = $allowedChildrenTypeIds;
        return $this;
    }

    /**
     * Pages with this type can only be added as children to parents with the
     * returned list of page type ids.
     *
     * If you return ['*'] generally all are allowed (Default). Return a list
     * of page type ids to restrict it to that page type ids.
     *
     * @return string[]
     */
    public function getAllowedParentTypeIds()
    {
        return $this->allowedParentTypeIds;
    }

    /**
     * @see self::getAllowedParentTypeIds()
     *
     * @param string[] $allowedParentTypeIds
     *
     * @return $this
     */
    public function setAllowedParentTypeIds(array $allowedParentTypeIds)
    {
        $this->allowedParentTypeIds = $allowedParentTypeIds;
        return $this;
    }

    /**
     * Return true if this page can be added to the root node. (At depth 0)
     *
     * @return bool
     */
    public function canBeRoot()
    {
        return $this->canBeRoot;
    }

    /**
     * @see self::canBeRoot()
     *
     * @param bool $canBeRoot
     *
     * @return $this
     */
    public function setCanBeRoot($canBeRoot)
    {
        $this->canBeRoot = $canBeRoot;
        return $this;
    }

    /**
     * Return the scope name in which pages of this type can be added. In most
     * cases there are two tree scopes (default and admin). An empty string or
     * a * wildcard means the type can be used in any scope. Any other string
     * restricts it to the mentioned scope.
     *
     * @return string
     */
    public function getRouteScope()
    {
        return $this->routeScope;
    }

    /**
     * @see self::getRouteScope()
     *
     * @param string $scope
     *
     * @return $this
     */
    public function setRouteScope($scope)
    {
        $this->routeScope = $scope;
        return $this;
    }

    /**
     * This is a special feature, in most cases you do not need it. Cmsable
     * routes first by a tree not routes.
     * But if someone calls a route directly (without a page) you can store an
     * array of route names here to match to that route name.
     * So if no page was found cmsable will find the route of that page. If then
     * any page in the tree has a page type that has the id of this route in its
     * reverse matches the first found page will match. (Event if the url did
     * not match that page)
     *
     * @return array
     */
    public function getRouteNamesForReverseMatch()
    {
        return $this->routeNamesForReverseMatch;
    }

    /**
     * @see self::getRouteNamesForReverseMatch()
     *
     * @param array|string $names
     *
     * @return $this
     */
    public function setRouteNamesForReverseMatch($names)
    {
        $this->routeNamesForReverseMatch = (array)$names;
        return $this;
    }

    /**
     * Return a class of an object that will manipulate the action (Controller)
     * before performing it.
     *
     * @see ActionConfigurator
     *
     * @return string
     */
    public function getActionConfiguratorClass()
    {
        return $this->actionConfiguratorClass;
    }

    /**
     * @see self::getActionConfiguratorClass()
     *
     * @param string $actionConfiguratorClass
     *
     * @return $this
     */
    public function setActionConfiguratorClass($actionConfiguratorClass)
    {
        $this->actionConfiguratorClass = $actionConfiguratorClass;
        return $this;
    }


}