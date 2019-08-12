<?php

use Ems\Tree\Eloquent\EloquentNode;

/**
 *  * Created by mtils on 17.11.18 at 13:39.
 **/

class EloquentPage extends EloquentNode implements Page
{
    /**
     * @var string
     */
    protected $contentKey = 'content';

    /**
     * @var string
     */
    protected $pageTypeIdKey = 'page_type_id';

    /**
     * @var string
     */
    protected $redirectTypeKey = 'redirect_type';

    /**
     * @var string
     */
    protected $redirectTargetKey = 'redirect_target';

    /**
     * @var string
     */
    protected $menuTitleKey = 'menu_title';

    /**
     * @var string
     */
    protected $titleKey = 'title';

    /**
     * @var string
     */
    protected $defaultContentType = 'text/html';

    /**
     * @var string
     */
    protected $locale = 'en_US';

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getContent()
    {
        return $this->getAttributeFromArray($this->contentKey);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getContentMimeType()
    {
        return $this->defaultContentType;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getPageTypeId()
    {
        return $this->getAttributeFromArray($this->pageTypeIdKey);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getRedirectType()
    {
        return $this->getAttributeFromArray($this->redirectTypeKey);
    }

    /**
     * {@inheritdoc}
     *
     * @return int|string
     */
    public function getRedirectTarget()
    {
        return $this->getAttributeFromArray($this->redirectTargetKey);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMenuTitle()
    {
        return $this->getAttributeFromArray($this->menuTitleKey);
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getAttributeFromArray($this->titleKey);
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
     * @see self::getLocale()
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

}