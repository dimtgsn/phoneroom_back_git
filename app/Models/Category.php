<?php

namespace App\Models;

use App\Utilities\TranslationIntoLatin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Meilisearch\Client;

class Category extends Model
{
    use HasFactory;

//    protected $table = 'categories';
//
////    public function shouldBeSearchable()
////    {
////        return !count($this->load('variants')->variants) && count($this->load('products')->products);
////    }
//
//    public function toSearchableArray()
//    {
//        $client = new Client('http://127.0.0.1:7700');
//        $client->index('categories')->updateFilterableAttributes([
//            'name',
////            'brands.name',
////            'variants'
//        ]);
//
//        $parentCategory = Category::query()
//            ->select('slug')
//            ->where('id', $this->parent_id)
//            ->first();
//
//        return [
//            'slug' => $this->slug,
//            'name' => $this->name,
//            'category_parent_slug' => $parentCategory->slug ?? null,
//            'brands' => json_decode($this->load('brands')->brands, true),
//            'product' => json_decode($this->load('products')->products, true),
////            'variants' => json_decode($this->variants, true),
////            'variants_json' => json_decode($this->variants_json,true),
//        ];
//    }

    protected $fillable = [
        'slug',
        'id',
        'name',
        'my_warehouse_id',
        'image',
        'parent_id',
        'brands'
    ];

    protected $with =[
//        'brands',
//        'variants'
//        "properties",
//        "options",
//        'products',
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Category $category) {
            $category->slug = $category->slug ?? str(TranslationIntoLatin::translate($category->name))->slug();
        });
    }

    public function brands(): belongsToMany
    {
        return $this->belongsToMany(Brand::class);
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class);
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(Variant::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
