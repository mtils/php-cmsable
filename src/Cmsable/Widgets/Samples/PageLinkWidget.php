<?php


namespace Cmsable\Widgets\Samples;

use Collection\Map\Extractor;
use FormObject\Form;
use Cmsable\Widgets\Contracts\WidgetItem;
use Symfony\Component\Translation\TranslatorInterface as Translator;
use Ems\App\Http\Forms\Fields\NestedSelectField;
use Cmsable\Model\SiteTreeModelInterface;
use Cmsable\Widgets\AbstractWidget;
use URL;
use Collection\StringList;


/**
 * This is a very simple widget which shows one Sentence in a box
 **/
class PageLinkWidget extends AbstractWidget
{

    public static $typeId = 'cmsable.widgets.samples.page-link';

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     **/
    protected $lang;

    protected $rules = [
        'text' => 'min:2|max:255',
        'link'  => 'required|numeric'
    ];

    protected $linkClasses;

    public function __construct(Translator $translator,
                                SiteTreeModelInterface $siteTree)
    {
        $this->lang = $translator;
        $this->siteTree = $siteTree;
        $this->linkClasses = new StringList(['page-link', 'aside-link']);
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
        return ['text' => ''];
    }
    
    /**
     * The css classes of the a tag
     *
     * @return \Collection\StringList
     **/
    public function linkClasses()
    {
        return $this->linkClasses;
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
            return '';
        }
        if (!is_numeric($data['link']) || !$data['link'] ) {
            return '';
        }
        if (!$page = $this->siteTree->pageById($data['link'])) {
            return '';
        }

        $text = isset($data['text']) && $data['text'] ? $data['text'] : $page->getMenuTitle();
        return '<a class="' . $this->linkClasses . '" href="' . URL::to($page) . "\">$text</a>";
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
        if (isset($data['link'])) {
            return $this->render($item);
        }
        $page = $this->siteTree->pageByPath('/');

        $text = isset($data['text']) && $data['text'] ? $data['text'] : $page->getMenuTitle();

        return "<a class=\"{$this->linkClasses}\" href=\"javascript: return false;\">$text</a>";

    }

    /**
     * {@inheritdoc}
     *
     * @param \Cmsable\Widgets\Contracts\WidgetItem $item
     * @return string
     **/
    public function renderForm(WidgetItem $item, $params=[])
    {
        $form = Form::create('link-widget');
        $siteTreeSelect = NestedSelectField::create('link');
        $siteTreeSelect->setModel($this->siteTree)
                       ->setSrc([], new Extractor('id','menu_title'));
        $form->push($siteTreeSelect);

        $form->push(Form::text('text'));

        $form->fillByArray($item->getData());
        return (string)$form;
    }

}