<?php
namespace MicroweberPackages\Product;

use Illuminate\Database\Eloquent\Model;
use MicroweberPackages\Content\Scopes\ProductScope;
use MicroweberPackages\ContentData\ContentData;

class Product extends Model
{
    protected $table = 'content';
    protected $primaryKey = 'id';

    protected $fillable = [
        'title',
        'url',
        'parent',
        'description',
        'position',
        'is_active',
        'is_deleted',
        'status'
    ];

    public $translatable = ['title','description','content','content_body'];

    public function setPrice($price) {
        $this->price = $price;
    }

    public function setSpecialPrice($price) {
        $this->special_price = $price;
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new ProductScope());
    }

    public function qty()
    {
        return $this->hasOne(ContentData::class, 'rel_id')->where('field_name', 'qty')->first();
    }

    public function sku()
    {
        return $this->hasOne(ContentData::class, 'rel_id')->where('field_name', 'sku')->first();
    }

    public function shippingWeight()
    {
        return $this->hasOne(ContentData::class, 'rel_id')->where('field_name', 'shipping_weight')->first();
    }

    public function shippingWidth()
    {
        return $this->hasOne(ContentData::class, 'rel_id')->where('field_name', 'shipping_width')->first();
    }

    public function shippingHeight()
    {
        return $this->hasOne(ContentData::class, 'rel_id')->where('field_name', 'shipping_height')->first();
    }

    public function shippingDepth()
    {
        return $this->hasOne(ContentData::class, 'rel_id')->where('field_name', 'shipping_depth')->first();
    }

    public function price()
    {
        return $this->hasOne(ProductPrice::class, 'rel_id');
    }

    public function specialPrice()
    {
        return $this->hasOne(ProductSpecialPrice::class, 'rel_id');
    }
}