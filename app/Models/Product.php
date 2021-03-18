<?php

namespace App\Models;

use Carbon\Carbon;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Product
 * 
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $images
 * @property string $video_url
 * @property int $price
 * @property int $category_id
 * @property bool $show_on_website
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \App\Models\Category $category
 * @property \Illuminate\Database\Eloquent\Collection $orders
 *
 * @package App\Models
 */
class Product extends Eloquent
{
    public const TABLE_NAME = 'products';

    public const TITLE_MAX_LENGTH = 50;
    public const DESCRIPTION_MAX_LENGTH = 500;
    public const DESCRIPTION_MIN_LENGTH = 10;
    public const IMAGES_MAX_COUNT = 3;

    /**
     * @var array
     */
	protected $casts = [
		'price' => 'int',
		'category_id' => 'int'
	];

    /**
     * @var array
     */
	protected $fillable = [
		'title',
		'description',
		'images',
        'video_link',
		'price',
		'category_id'
	];

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Product
     */
    public function setTitle(string $title): Product
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Product
     */
    public function setDescription(string $description): Product
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImages(): ?string
    {
        return $this->images;
    }

    /**
     * @param string $images
     *
     * @return Product
     */
    public function setImages(string $images): Product
    {
        $this->images = $images;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getVideoUrl(): ?string
    {
        return $this->video_url;
    }

    /**
     * @param string $videoUrl
     *
     * @return Product
     */
    public function setVideoUrl(string $videoUrl): Product
    {
        $this->video_url = $videoUrl;

        return $this;
    }

    /**
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
    }

    /**
     * @param int $price
     *
     * @return Product
     */
    public function setPrice(int $price): Product
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->category_id;
    }

    /**
     * @param int $categoryId
     *
     * @return Product
     */
    public function setCategoryId(int $categoryId): Product
    {
        $this->category_id = $categoryId;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShowOnWebsite(): bool
    {
        return $this->show_on_website;
    }

    /**
     * @param bool $showOnWebsite
     *
     * @return Product
     */
    public function setShowOnWebsite(bool $showOnWebsite): Product
    {
        $this->show_on_website = $showOnWebsite;

        return $this;
    }

    /**
     * @return Carbon
     */
    public function getCreatedAt(): Carbon
    {
        return $this->created_at;
    }

    /**
     * @return Carbon
     */
    public function getUpdatedAt(): Carbon
    {
        return $this->updated_at;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
	public function category()
	{
		return $this->belongsTo(Category::class);
	}

    /**
     * @return Category
     */
	public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
	public function orders()
	{
		return $this->hasMany(RentOrder::class);
	}
}
