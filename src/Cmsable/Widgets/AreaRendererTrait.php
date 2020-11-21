<?php


namespace Cmsable\Widgets;

use Cmsable\Widgets\Repositories\WidgetTool;
use OutOfBoundsException;
use RuntimeException;
use Cmsable\Widgets\Contracts\Area as AreaContract;
use Cmsable\Widgets\Contracts\AreaRepository;
use Cmsable\Widgets\Contracts\Renderer;
use Cmsable\Widgets\Contracts\WidgetItem as ItemContract;
use Cmsable\Widgets\Contracts\Registry as RegistryContract;

/**
 * This trait helps to build renderes. All you have to implement is the actual renderArea
 * method
 **/
trait AreaRendererTrait
{

    /**
     * @var array
     **/
    protected $_renderers;

    /**
     * @var array
     **/
    protected $_templates = [];

    /**
     * @var array
     **/
    protected $_editTemplates = [];

    protected $_registry;

    /**
     * @return \Cmsable\Widgets\Contracts\Registry
     **/
    public function registry()
    {
        if (!$this->_registry) {
            throw new RuntimeException("Please assign a registry to " . get_class($this));
        }

        return $this->_registry;
    }

    /**
     * Set a registry. Usually inside a constructor of the class using this
     * trait
     *
     * @param \Cmsable\Widgets\Contracts\Registry $registry
     * @return self
     **/
    public function setRegistry(RegistryContract $registry)
    {
        $this->_registry = $registry;
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return string
     **/
    public function render(ItemContract $item)
    {
        $string = $this->registry()->get($item->getTypeId())->render($item);
        $anchor = WidgetTool::ANCHOR_PREFIX . $item->getId();
        return "<a class=\"widget-anchor\" id=\"$anchor\"></a>$string";
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return string
     **/
    public function renderPreview(ItemContract $item)
    {
        return $this->registry()->get($item->getTypeId())->renderPreview($item);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return string
     **/
    public function renderForm(ItemContract $item, $params=[])
    {
        return $this->registry()->get($item->getTypeId())->renderForm($item, $params);
    }

    /**
     * Return a template for area named $name
     *
     * @param string $name
     * @return string
     **/
    public function getTemplate($name=AreaRepository::CONTENT)
    {
        if (isset($this->_templates[$name])) {
            return $this->_templates[$name];
        }
        throw new OutOfBoundsException("No template found for area named '$name'");
    }

    /**
     * Set a template for an area named $name
     *
     * @param string $template
     * @param string $name (optional)
     * @return self
     **/
    public function setTemplate($template, $name=AreaRepository::CONTENT)
    {
        $this->_templates[$name] = $template;
        return $this;
    }

    /**
     * Return a edit template for area named $name
     *
     * @param string $name
     * @return string
     **/
    public function getEditTemplate($name=AreaRepository::CONTENT)
    {
        if (isset($this->_editTemplates[$name])) {
            return $this->_editTemplates[$name];
        }
        throw new OutOfBoundsException("No edit template found for area named '$name'");
    }

    /**
     * Set a edit template for an area named $name
     *
     * @param string $template
     * @param string $name (optional)
     * @return self
     **/
    public function setEditTemplate($template, $name=AreaRepository::CONTENT)
    {
        $this->_editTemplates[$name] = $template;
        return $this;
    }
}