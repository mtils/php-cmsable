<?php


namespace Cmsable\Widgets\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Cmsable\Http\Resource\CleanedRequest;
use Ems\App\Helpers\ProvidesTexts;

use Cmsable\View\Contracts\Notifier;
use Cmsable\Widgets\Contracts\Registry;
use Cmsable\Widgets\Contracts\WidgetItemRepository;

class WidgetItemController extends Controller
{

    /**
     * @var \Cmsable\Widgets\Contracts\Registry
     **/
    protected $registry;
    
    /**
     * @var WidgetItemRepository
     **/
    protected $repository;

    /**
     * @param \Cmsable\Widgets\Contracts\Registry $registry
     **/
    public function __construct(Registry $registry, WidgetItemRepository $repository)
    {
        $this->registry = $registry;
        $this->repository = $repository;
    }

    /**
     * Shows a list of all available widgets
     *
     * @return Illuminate\Contracts\View\View
     **/
    public function index()
    {
        return view('widgets.index')->withWidgets($this->registry->all());
    }

    /**
     * Shows a detailed view of one widget (for example a preview)
     *
     * @param string $typeId
     * @return Illuminate\Contracts\View\View
     **/
    public function show($typeId)
    {
        return view('widgets.show')->withWidget($this->registry->get($typeId));
    }

    public function create($typeId)
    {
        $vars = [
            'widget' => $this->registry->get($typeId),
            'widgetItem' => $this->repository->make($typeId)
        ];
        return view('widget-items.edit', $vars);
    }

}