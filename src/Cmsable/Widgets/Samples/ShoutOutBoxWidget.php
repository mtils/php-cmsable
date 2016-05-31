<?php


namespace Cmsable\Widgets\Samples;

use Collection\Map\Extractor;
use FormObject\Form;
use Cmsable\Widgets\Contracts\Widget;
use Cmsable\Widgets\Contracts\WidgetItem;
use Cmsable\Widgets\Contracts\AreaRepository;
use Illuminate\Contracts\Validation\ValidationException;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Symfony\Component\Translation\TranslatorInterface as Translator;
use Ems\App\Http\Forms\Fields\NestedSelectField;
use Cmsable\Model\SiteTreeModelInterface;



/**
 * This is a very simple widget which shows one Sentence in a box
 **/
class ShoutOutBoxWidget implements Widget
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

    protected $validationFactory;

    public function __construct(ValidationFactory $validationFactory, Translator $translator,
                                SiteTreeModelInterface $siteTree)
    {
        $this->validationFactory = $validationFactory;
        $this->lang = $translator;
        $this->siteTree = $siteTree;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getTypeId()
    {
        return static::$typeId;
    }

    /**
     * {@inheritdoc}
     *
     * @param array
     * @return bool
     * @throws \Illuminate\Contracts\Validation\ValidationException
     **/
    public function validate(array $data)
    {
        $validator = $this->validationFactory->make($data, $this->rules);
        if ($validator->passes()) {
            return true;
        }
        throw new ValidationException($validator);
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
     * @return string
     **/
    public function category()
    {
        return 'banners';
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     **/
    public function getMaxColumnSpan()
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     **/
    public function getMaxRowSpan()
    {
        return 2;
    }

    /**
     * {@inheritdoc}
     *
     * @return bool
     **/
    public function isEditable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $pageTypeId
     * @param string $areaName
     * @return bool
     **/
    public function isAllowedOn($pageTypeId, $areaName=AreaRepository::CONTENT)
    {
        return true;
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
    public function renderPreview(WidgetItem $item)
    {
        return $this->render($item);
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

    protected function trKey($key, $namespace='')
    {
        return $namespace . 'widgets.' . str_replace('.','/',$this->getTypeId()) . ".$key";
    }
}