<?php

namespace App\Models;

use Carbon\Carbon;
use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class Category
 * 
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $image
 * @property bool $show_in_bottom
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * 
 * @property \Illuminate\Database\Eloquent\Collection $products
 *
 * @package App\Models
 */
class Category extends Eloquent
{
    public const TABLE_NAME = 'categories';

    public const TITLE_MAX_LENGTH = 30;

    public const SLUG_MAX_LENGTH = 30;
    public const SLUG_MIN_LENGTH = 5;

    /**
     * @var array
     */
	protected $fillable = [
		'title'
	];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
	public function products()
	{
		return $this->hasMany(Product::class);
	}

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
     * @return Category
     */
    public function setTitle(string $title): Category
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     *
     * @return Category
     */
    public function setSlug(string $slug): Category
    {
        $this->slug = strtolower($slug);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getImage(): ?string
    {
        return $this->image;
    }

    /**
     * @param string $image
     *
     * @return Category
     */
    public function setImage(string $image): Category
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return bool
     */
    public function isShownInBottom(): bool
    {
        return $this->show_in_bottom;
    }

    /**
     * @param bool $showInBottom
     *
     * @return Category
     */
    public function setShowInBottom(bool $showInBottom): Category
    {
        $this->show_in_bottom = $showInBottom;

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
}
