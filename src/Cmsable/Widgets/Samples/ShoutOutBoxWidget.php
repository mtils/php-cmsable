<?php


namespace Cmsable\Widgets\Samples;

use Collection\Map\Extractor;
use FormObject\Form;
use Cmsable\Widgets\Contracts\WidgetItem;
use Illuminate\Translation\Translator;
use Ems\App\Http\Forms\Fields\NestedSelectField;
use Cmsable\Model\SiteTreeModelInterface;
use Cmsable\Widgets\AbstractWidget;
use URL;


/**
 * This is a very simple widget which shows one Sentence in a box
 **/
class ShoutOutBoxWidget extends AbstractWidget
{

    public static $typeId = 'cmsable.widgets.samples.shout-out-box';

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     **/
    protected $lang;

    protected $rules = [
        'shout' => 'required|min:2|max:255',
        'link'  => 'numeric'
    ];

    public function __construct(Translator $translator,
                                SiteTreeModelInterface $siteTree)
    {
        $this->lang = $translator;
        $this->siteTree = $siteTree;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     **/
    public function rules()
    {
        return $this->rules;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     **/
    public function defaultData()
    {
        return [ 'shout' => $this->lang->get($this->trKey('default-shout','ems::')) ];
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return string
     **/
    public function render(WidgetItem $item)
    {
        $data = $item->getData();
        if (!isset($data['link'])) {
            return $this->renderPreview($item);
        }
        if (!is_numeric($data['link']) || !$data['link'] ) {
            return $this->renderPreview($item);
        }
        if (!$page = $this->siteTree->pageById($data['link'])) {
            return $this->renderPreview($item);
        }
        return '<a href="' . URL::to($page) . '">' . $this->renderPreview($item) . '</a>';
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return string
     **/
    public function renderPreview(WidgetItem $item)
    {
        $data = $item->getData();
        if (!isset($data['shout'])) {
            return '';
        }
        $shout = $data['shout'];
        return "<h1 class=\"shout-out\">$shout</h1>";
    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return string
     **/
    public function renderForm(WidgetItem $item, $params=[])
    {
        $form = Form::create('shout-out-box');
        $form->push(Form::text('shout')->setMultiline(true));
        $siteTreeSelect = NestedSelectField::create('link');
        $siteTreeSelect->setModel($this->siteTree)
                       ->setSrc([], new Extractor('id','menu_title'));
        $form->push($siteTreeSelect);
        $form->fillByArray($item->getData());
        return (string)$form;
    }

}