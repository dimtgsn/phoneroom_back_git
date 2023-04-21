<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Meilisearch\Client;

class CategoryProduct extends Model
{
    use HasFactory, Searchable;

    protected $table = 'category_products';

//    public function searchableAs()
//    {
//        return 'category_'.$this->category_id;
//    }
//    public function searchableUsing(): Engine
//    {
//        return app(EngineManager::class)->engine('meilisearch');
//    }

//    public function toSearchableArray()
//    {
//        $client = new Client('http://127.0.0.1:7700');
//        $client->index('category_variant')->updateFilterableAttributes([
//            'brand',
//            'price',
//            'in_stock',
//            'options_names',
//            'options_values',
//            'tags',
//        ]);
//
//        $client->index('category_variant')->updateDisplayedAttributes([
//            'category_slug',
//            'tags',
//
//            'in_stock',
//            'category_parent_slug',
//            'product',
//            'options_names',
//            'options_values' ,
//            'price',
//            'brand'
//        ]);
//
//        $category = Category::find($this->category_id);
//
//        $parentCategory = Category::query()
//            ->select('slug')
//            ->where('id', $category->parent_id)
//            ->first();
//        $variant = Variant::find($this->variant_id);
//
//        $option = json_decode($variant->variants_json, true)['options'];
//        $options['name'] = [];
//        $options['value'] = [];
//        foreach ($option as $name => $val){
//            $options['name'][] = $name;
//            $options['value'][$name] = $val;
//        }
//
//        $client->index('category_variant')->updateSortableAttributes([
//            'product.rating',
//        ]);
//
//        foreach ($category->products as $product){
//            foreach ($product->tags as $tag){
//                $tags[] = $tag->name;
//            }
//        }
//
//        return [
//            'category_slug' => $category->slug,
//            'in_stock' => json_decode($variant->variants_json, true)['units_in_stock'] != 0,
//            'category_parent_slug' => $parentCategory->slug,
//            'product' => json_decode($variant->variants_json, true),
////            'products' => json_decode($category->products, true),
//            'tags' => $tags,
//            'options_names' => $options['name'],
//            'options_values' => $options['value'],
//            'price' => json_decode($variant->variants_json, true)['price'],
//            'brand' => json_decode($variant->variants_json, true)['brand'],
//        ];
//    }

    protected $fillable = [
        'category_id',
        'product_id',
    ];
}
