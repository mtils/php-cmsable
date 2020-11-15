<?php


namespace Cmsable\Widgets\Http\Controllers;

use Illuminate\Contracts\View\View;
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
     * @return View
     **/
    public function index(Request $request)
    {
        $criteria = [];
        foreach (['area_id', 'type_id'] as $key) {
            if ($value = $request->get($key)) {
                $criteria[$key] = $value;
            }
        }
        $vars = [
            'widgetRegistry' => $this->registry,
            'handle' => $request->input('handle'),
            'inputPrefix' => $request->input('input_prefix'),
            'items' => $this->repository->search($criteria)
        ];
        return view('widget-items.index', $vars);
    }

    /**
     * Shows a detailed view of one widget (for example a preview)
     *
     * @param Request $request
     * @param string $id
     *
     * @return View
     */
    public function show(Request $request, $id)
    {
        $item = $this->repository->find($id);
        $widget = $this->registry->get($item->getTypeId());
        $handle = $request->get('handle');
        $inputPrefix = $request->get('input_prefix');

        $vars = [
            'widgetItem'        => $item,
            'widget'            => $widget,
            'hideCloseButton'   => true,
            'draggable'         => false,
            'editCall'          => 'changeWidgetItem(document.getElementById(\'$handle\'))',
            'handle'            => $handle,
            'inputPrefix'       => $inputPrefix
        ];

        return view('widget-items.partials.boxed-widget-item', $vars);
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