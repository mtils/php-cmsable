<?php


namespace Cmsable\Widgets\Http\Controllers;

use Cmsable\Widgets\Repositories\WidgetTool;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Validation\ValidationException;
use Cmsable\Http\Resource\CleanedRequest;
use Ems\App\Helpers\ProvidesTexts;

use Cmsable\View\Contracts\Notifier;
use Cmsable\Widgets\Contracts\Registry;
use Cmsable\Widgets\Contracts\WidgetItemRepository;

use function str_replace;

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
     * @param WidgetItemRepository $itemRepository
     */
    public function __construct(Registry $registry, WidgetItemRepository $itemRepository)
    {
        $this->registry = $registry;
        $this->itemRepository = $itemRepository;
    }

    /**
     * Shows a list of all available widgets
     *
     * @return View
     **/
    public function index(Request $request)
    {

        $vars = [
            'widgets' => $this->registry->all(),
            'handle' => $request->input('handle'),
            'inputPrefix' => $request->input('input_prefix')
        ];

        //$this->itemRepository

        return view('widgets.index', $vars);
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
     * @param Request $request
     * @param WidgetTool $tool
     * @param string $typeId
     *
     * @return View
     * @throws \Throwable
     */
    public function showIfValid(Request $request, WidgetTool $tool, $typeId)
    {

        $data = $request->all();
        $framed = false;

        if (isset($data['framed'])) {
            $framed = $data['framed'] == 'false' ? false : (bool)$data['framed'];
            unset($data['framed']);
        }

        $handle = $data['handle'];
        $inputPrefix = $data['input_prefix'];

        unset($data['handle']);
        unset($data['input_prefix']);

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
            'framed' => $framed,
            'handle' => $handle,
            'inputPrefix' => $inputPrefix,
            'previewMode' => 'empty-iframe'
        ];

        $response = [
            'preview'   => (string)$widget->render($item),
            'data'      => $item->getData(),
            'iframe-id' => $tool->iframeId($item, $handle),
            'css'       => config('cmsable.cms-editor-css')
        ];

        if ($framed) {
            $response['frame'] = (string)view('widget-items.partials.boxed-widget-item', $vars)->render();
        }
        return response()->json($response);
    }

    public function createItem(Request $request, $typeId)
    {
        $vars = [
            'widget' => $this->registry->get($typeId),
            'widgetItem' => $this->itemRepository->make($typeId),
            'handle' => $request->input('handle'),
            'inputPrefix' => $request->input('input_prefix')
        ];
        return view('widget-items.create', $vars);
    }

    public function editPreview(Request $request, $typeId)
    {

        $vars = [
            'widget' => $this->registry->get($typeId),
            'widgetItem' => $this->itemRepository->make($typeId, $request->all()),
            'handle' => $request->input('handle'),
            'inputPrefix' => $request->input('input_prefix')
        ];
        return view('widget-items.create', $vars);
    }

    public function editItem(Request $request, $typeId, $itemId)
    {

        $item = $this->itemRepository->find($itemId);

        $vars = [
            'widget' => $this->registry->get($item->getTypeId()),
            'widgetItem' => $item,
            'handle' => $request->input('handle')
        ];
        return view('widget-items.edit', $vars);
    }

}