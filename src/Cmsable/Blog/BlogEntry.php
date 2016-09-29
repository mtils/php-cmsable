<?php


namespace Cmsable\Blog;


use DateTime;
use Cmsable\Blog\Contracts\BlogEntry as BlogEntryContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use FileDB\Model\EloquentFile;
use App\User;
use Ems\Contracts\Model\Relation\Tag\HoldsTags;
use Ems\Model\Relation\Tag\HoldsTagsTrait;
use Ems\Contracts\Core\AppliesToResource;

class BlogEntry extends Model implements BlogEntryContract, HoldsTags, AppliesToResource
{

    use SoftDeletes;
    use HoldsTagsTrait;

    /**
    * The database table used by the model.
    *
    * @var string
    */
    protected $table = 'blog_entries';

    /**
    * The attributes that are mass assignable.
    *
    * @var array
    */
    protected $guarded = ['owner_id', 'id'];

    /**
     * {@inheritdoc}
     *
     * @var array
     */
    protected $dates = ['blog_date', 'publish_at'];

    /**
     * {@inheritdoc]
     *
     * @var array
     */
    protected $attributes = ['priority'=>8];

    /**
     * {@inheritdoc}
     *
     * @return mixed (int)
     * @see \Ems\Contracts\Core\Identifiable
     **/
    public function getId()
    {
        return $this->getKey();
    }

    /**
     * {@inheritdoc}
     *
     * @param int $size (optional)
     * @return string
     * @see \Ems\Contracts\Core\HasFrontCover
     **/
    public function getFrontCover($size=0)
    {
        if (!$this->preview_image_id && !$this->image_id) {
            return '';

        }

        if ($this->preview_image && $size <= 128) {
            return $this->preview_image->url;
        }

        if (!$this->image) {
            return '';
        }

        return $this->image->url;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     * @see \Ems\Contracts\Core\HasContent
     **/
    public function getContent()
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     * @see \Ems\Contracts\Core\HasContent
     **/
    public function getContentMimeType()
    {
        return 'text/html';
    }

    /**
     * {@inheritdoc}
     *
     * @return \Ems\Contracts\Core\Identifiable
     * @see \Ems\Contracts\Core\HasOwner
     **/
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * {@inheritdoc}
     *
     * @return \Ems\Contracts\Core\Identifiable
     **/
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getMenuTitle()
    {
        if ($this->menu_title) {
            return $this->menu_title;
        }
        return $this->title;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getTopic()
    {
        return $this->topic;
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     **/
    public function getUrlSegment()
    {
        return $this->url_segment;
    }

    /**
     * {@inheritdoc}
     *
     * @return string|null
     **/
    public function getPreviewContent()
    {
        return $this->preview_content;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $context (optional)
     * @return string
     **/
    public function isVisible($context=null)
    {
        return $this->visibility != 0;
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     **/
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     *
     * @return \DateTime
     **/
    public function getPublishAt()
    {
        return $this->publish_at;
    }

    /**
     * {@inheritdoc}
     *
     * @return \DateTime
     **/
    public function getDate()
    {
        return $this->blog_date;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function image()
    {
        return $this->belongsTo(EloquentFile::class,'image_id');
    }

    public function preview_image()
    {
        return $this->belongsTo(EloquentFile::class,'preview_image_id');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Ems\Contracts\Core\AppliesToResource
     * @return string
     **/
    public function resourceName()
    {
        return 'blog-entries';
    }

}
