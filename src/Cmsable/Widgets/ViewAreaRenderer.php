<?php


namespace Cmsable\Widgets;

use OutOfBoundsException;

use Cmsable\Widgets\Contracts\Registry as RegistryContract;
use Cmsable\Widgets\Contracts\Area as AreaContract;
use Cmsable\Widgets\Contracts\Renderer;
use Cmsable\Widgets\Contracts\AreaRenderer;
use Illuminate\Contracts\View\Factory as ViewFactory;

class ViewAreaRenderer implements AreaRenderer
{

    use AreaRendererTrait;

    /**
     * @var \Illuminate\Contracts\View\Factory
     **/
    protected $view;

    /**
     * @param Illuminate\Contracts\View\Factory $view
     **/
    public function __construct(ViewFactory $view, RegistryContract $registry)
    {
        $this->view = $view;
        $this->setRegistry($registry);
    }

    /**
     * {@inheritdoc}
     *
     * * @param \Cmsable\Widgets\Contracts\Area
     * @param array $vars (optional) view variabled
     * @return string
     **/
    public function renderArea(AreaContract $area, array $vars=[])
    {
        return $this->renderByView($this->getTemplate($area->getName()), $area, $vars);
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\Area $area
     * @param array $vars (optional) view variabled
     * @return string
     **/
    public function renderEditArea(AreaContract $area, array $vars=[])
    {
        return $this->renderByView($this->getEditTemplate($area->getName()), $area, $vars);
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @return string
     **/
    public function getTemplate($name=AreaRepository::CONTENT)
    {
        if (isset($this->_templates[$name])) {
            return $this->_templates[$name];
        }
        return 'widgets.default';
    }

    /**
     * {@inheritdoc}
     *
     * @param string $name
     * @return string
     **/
    public function getEditTemplate($name=AreaRepository::CONTENT)
    {
        if (isset($this->_editTemplates[$name])) {
            return $this->_editTemplates[$name];
        }
        return 'widgets.edit';
    }

    /**
     * Render through the laravel view
     *
     * @param string $template
     * @param \Cmsable\Widgets\Contracts\Area $area
     * @param array $vars
     * @return string
     **/
    protected function renderByView($template, AreaContract $area, array $vars=[])
    {
        $viewVars = [
            'area'      => $area,
            'widgets'   => $this
        ];

        return $this->view->make($template, $viewVars, $vars)->render();

    }

}