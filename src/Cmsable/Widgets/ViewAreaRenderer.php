<?php


namespace Cmsable\Widgets;

use OutOfBoundsException;

use Cmsable\Widgets\Contracts\Registry as RegistryContract;
use Cmsable\Widgets\Contracts\Area as AreaContract;
use Cmsable\Widgets\Contracts\Renderer;
use Cmsable\Widgets\Contracts\AreaRenderer;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Cmsable\Widgets\Contracts\AreaRepository;

class ViewAreaRenderer implements AreaRenderer
{

    use AreaRendererTrait;

    /**
     * @var ViewFactory
     **/
    protected $view;

    /**
     * ViewAreaRenderer constructor.
     * @param ViewFactory $view
     * @param RegistryContract $registry
     */
    public function __construct(ViewFactory $view, RegistryContract $registry)
    {
        $this->view = $view;
        $this->setRegistry($registry);
    }

    /**
     * {@inheritdoc}
     *
     * * @param AreaContract
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
     * @param AreaContract $area
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
     * @param AreaContract $area
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