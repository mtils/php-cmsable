<?php


namespace Cmsable\Widgets\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Validation\ValidationException;
use Cmsable\Http\Resource\CleanedRequest;
use Ems\App\Helpers\ProvidesTexts;

use Cmsable\View\Contracts\Notifier;
use Cmsable\Widgets\Contracts\Registry;
use Cmsable\Widgets\Contracts\WidgetItemRepository;

class WidgetController extends Controller
{

    /**
     * @var \Cmsable\Widgets\Contracts\Registry
     **/
    protected $registry;

    /**
     * @var \Cmsable\Widgets\Contracts\WidgetItemRepository
     **/
    protected $itemRepository;

    /**
     * @param \Cmsable\Widgets\Contracts\Registry $registry
     **/
    public function __construct(Registry $registry, WidgetItemRepository $itemRepository)
    {
        $this->registry = $registry;
        $this->itemRepository = $itemRepository;
    }

    /**
     * Shows a list of all available widgets
     *
     * @return Illuminate\Contracts\View\View
     **/
    public function index(Request $request)
    {
        return view('widgets.index')->withWidgets($this->registry->all())->withHandle($request->input('handle'));
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

    /**
     *
     * @param string $typeId
     * @return Illuminate\Contracts\View\View
     **/
    public function showIfValid(Request $request, $typeId)
    {

        $data = $request->all();
        $framed = false;

        if (isset($data['framed'])) {
            unset($data['framed']);
            $framed = true;
        }

        $widget = $this->registry->get($typeId);

        try {
            $widget->validate($data);
        } catch (ValidationException $e) {
            return response()->json($e->errors()->toArray(), 400);
        }

        $item = $this->itemRepository->make($typeId, $data);

        $vars = [
            'widget' => $widget,
            'widgetItem' => $item,
            'framed' => $framed
        ];
        return view('widget-items.show', $vars);
    }

    public function createItem(Request $request, $typeId)
    {
        $vars = [
            'widget' => $this->registry->get($typeId),
            'widgetItem' => $this->itemRepository->make($typeId),
            'handle' => $request->input('handle')
        ];
        return view('widget-items.edit', $vars);
    }

}