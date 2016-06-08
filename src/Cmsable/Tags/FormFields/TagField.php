<?php


namespace Cmsable\Tags\FormFields;


use FormObject\Field\SelectManyField;
use Ems\Contracts\Core\AppliesToResource;
use Ems\Contracts\Model\Relation\Tag\GlobalTaggingRepository;
use Collection\Map\Extractor;
use FormObject\Field\SelectableProxy;
use Symfony\Component\Translation\TranslatorInterface as Lang;

class TagField extends SelectManyField
{

    protected $repo;

    protected $modelValue;

    protected $lang;

    public function __construct(GlobalTaggingRepository $repo, Lang $lang)
    {
        $this->repo = $repo;
        $this->lang = $lang;
        $this->manualExtractor = new Extractor('getId()', 'getName()');
        $this->name = 'tags__ids';
        $this->title = $lang->get('ems::forms.base.tag-field.title');
        $this->desciption = $lang->get('ems::forms.base.tag-field.desciption');
    }

    public function getSrc()
    {
        if ($this->src === null) {
            $this->fillByRepository();
        }
        return $this->src;
    }

    public function getValue(){

        if ($this->value !== null) {
            return $this->value;
        }

        if ($this->modelValue !== null) {
            return $this->modelValue;
        }

        $this->modelValue = $this->getValueFromRepository();
        return $this->modelValue;
    }

    public function isItemSelected(SelectableProxy $item){

        $value = $this->getValue();

        if(!$value){
            return FALSE;
        }

        foreach($value as $key){
            if($item->getKey() == $key){
                return TRUE;
            }
        }
        return FALSE;
    }

    protected function fillByRepository()
    {
        $this->src = $this->getItemsFromRepository();
    }

    protected function getItemsFromRepository()
    {
        if (!$this->form) {
            return $this->repo->all();
        }

        if (!$model = $this->form->getModel()) {
            return $this->repo->all();
        }

        if (!$model instanceof AppliesToResource) {
            return $this->repo->all();
        }

        return $this->repo->all();

        return $this->repo->by($model)->all();
    }

    protected function getValueFromRepository()
    {
        if (!$this->form) {
            return;
        }

        if (!$model = $this->form->getModel()) {
            return;
        }

        if (!$model instanceof AppliesToResource) {
            return;
        }

        if ($tags = $model->getTags()) {
            return $this->ids($tags);
        }
        $holders = [$model];
        $this->repo->attachTags($model);

        if ($tags = $model->getTags()) {
            return $this->ids($tags);
        }

        return [];
    }
    
    protected function ids($tags)
    {
        $ids = [];
        foreach ($tags as $tag) {
            $ids[] = $tag->getId();
        }
        return $ids;
    }

}