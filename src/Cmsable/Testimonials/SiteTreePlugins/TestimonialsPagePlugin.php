<?php


namespace Cmsable\Testimonials\SiteTreePlugins;


use Cmsable\Controller\SiteTree\Plugin\ConfigurablePlugin;
use FormObject\Form;
use FormObject\FieldList;
use Cmsable\Model\SiteTreeNodeInterface as SiteTreeNode;
use Illuminate\Contracts\Container\Container;
use Lang;

class TestimonialsPagePlugin extends ConfigurablePlugin
{

    /**
     * @var \Illuminate\Contracts\Container\Container
     **/
    protected $app;

    protected $tagFieldClass = 'Cmsable\Tags\FormFields\TagField';

    protected $tagRepoInterface = 'Ems\Contracts\Model\Relation\Tag\GlobalTaggingRepository';

    /**
     * @param \Illuminate\Contracts\Container\Container $app
     **/
    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     *
     * @param FormObject\FieldList
     * @return void
     **/
    public function modifyFormFields(FieldList $fields, SiteTreeNode $page)
    {
        if (!$this->isTagExtensionLoaded()) {
            return;
        }

        $mainFields = $fields->get('main');
        $mainFields->push($this->createTagFilterField());
    }

    protected function isTagExtensionLoaded()
    {
        return $this->app->bound($this->tagRepoInterface);
    }

    protected function createTagFilterField()
    {
        return $this->app->make($this->tagFieldClass)
                         ->setName($this->fieldName('filter_tags_ids'))
                         ->setTitle(Lang::get('ems::sitetree-plugins.testimonials-page.filter_tags_ids.title'))
                         ->setOwner($this);
    }

}