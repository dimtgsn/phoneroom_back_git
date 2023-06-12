<?php

namespace App\Models;

use App\Utilities\TranslationIntoLatin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use HasFactory;

//    public function searchableAs()
//    {
//        return 'product_index';
//    }
//
//    public function toSearchableArray()
//    {
//        return [
//            ''
//        ];
//        $array = $this->toArray();
//
//        $array = json_decode($array->variants, true);
//
//        return $array;
//    }

    protected $with = [
        'images',
        'category',
        'brand',
        'tags',
        'property',
        'option',
        'variants',
        'baskets',
        'enter',
    ];

    protected $fillable = [
        'slug',
        'name',
        'image',
        'description',
        'old_price',
        'price',
        'units_in_stock',
        'rating',
        'category_id',
        'brand_id',
        'tag_id',
        'exported',
        'vat',
        'sku',
        'min_price',
        'purchase_price',
        'min_balance',
        'country',
        'published',
        'my_warehouse_id',
    ];


    protected static function boot()
    {
        parent::boot();

        static::creating(function (Product $product) {
            $translation = new TranslationIntoLatin();

            $product->slug = $product->slug ?? str($translation->translate($product->name))->slug();
//            $variants = json_decode($product->variants, true);
//            for ($i=0; $i<count($variants); $i++){
//                $variants[$i]['id'] = str($product->id).'00'.str($i+1);
//                $variants[$i]['slug'] = $variants[$i]['slug'] ?? str($translation->translate($variants[$i]['name']))->slug();
//                $variants[$i]['sku'] = trim(str_replace(" ", "-",strtoupper($translation->translate($product->name.' '.$variants[$i]['id'].' '.$product->category_id.' '.$product->brand_id))));
//                $variants_json[] = $variants[$i];
//            }
//            $product->variants = json_encode($variants_json, JSON_UNESCAPED_UNICODE);
        });

//        static::updating(function (Product $product){
//            $translation = new TranslationIntoLatin();
//
//            if ($product->variants){
//                $variants = json_decode($product->variants, true);
//                $arr = array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5);
//
//
//                for ($i=0; $i<count($variants); $i++){
//                    $variants[$i]['id'] = str($product->id).'00'.str($i+1);
//                    $variants[$i]['slug'] = $variants[$i]['slug'] ?? str($translation->translate($product->slug.' '.$variants[$i]['name']))->slug();
//                    $variants[$i]['sku'] = trim(str_replace(" ", "-",strtoupper($translation->translate($product->name.' '.$variants[$i]['id'].' '.$product->category_id.' '.$product->brand_id))));
//                    $variants_json[$i] = $variants[$i];
//                }
//                $product->variants = json_encode($variants_json, JSON_UNESCAPED_UNICODE);
//            }
//        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class);
    }

    public function property(): HasOne
    {
        return $this->hasOne(Property::class);
    }

    public function enter(): HasOne
    {
        return $this->hasOne(Enter::class);
    }

    public function option(): HasOne
    {
        return $this->hasOne(Option::class);
    }

    public function baskets(): BelongsToMany
    {
        return $this->belongsToMany(Basket::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

}

