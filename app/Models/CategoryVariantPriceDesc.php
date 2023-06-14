<?php

namespace App\Models;

use App\Utilities\TranslationIntoLatin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Meilisearch\Client;

class CategoryVariantPriceDesc extends Model
{
    use HasFactory, Searchable;

    protected $table = 'categories';

    public function searchableAs()
    {
        return 'category_variant_price_desc';
    }

    public function toSearchableArray()
    {
        $client = new Client('http://127.0.0.1:7700');

        $category = Category::find($this->id);
        $translation = new TranslationIntoLatin();

        $parentCategory = Category::query()
            ->select('slug')
            ->where('id', $category->parent_id)
            ->first();

        foreach ($category->products as $product){
            foreach ($product->tags as $tag){
                $tags[] = $tag->name;
            }
        }

        if (count($category->products)){
            if(count($category->load('variants')->variants)){
                $category_variants_json =  json_decode($category->load('variants')->variants->pluck('variants_json'), true);
                usort($category_variants_json, function ($a, $b) {
                    return (int)json_decode($a, true)['price'] < (int)json_decode($b, true)['price'];
                });
                foreach ($category_variants_json as $variant_price_desc){
                    $option = json_decode($variant_price_desc, true)['options'];

                    $variant_price_desc = json_decode($variant_price_desc, true);
                    unset($variant_price_desc['min_price']);
                    unset($variant_price_desc['min_balance']);
                    unset($variant_price_desc['purchase_price']);
                    unset($variant_price_desc['country']);
                    $variant_price_desc['quantity'] = 1;

                    $options['name'] = [];
                    $options['value'] = [];
                    foreach ($option as $name => $val){
                        $options['name'][] = $name;
                        $name = str_replace(" ", '', $name);
                        $name = preg_replace('/[^ a-zа-яё\d]/ui', '',$name );
                        $options['value'] += array($translation->translate($name) => $val);
                    }
                    $client->index('category_variant_price_desc')->updateDocuments([
                        'id' => $variant_price_desc['id'],
                        'category_slug' => $category->slug,
                        'in_stock' => $variant_price_desc['units_in_stock'] != 0,
                        'with_old_price' => $variant_price_desc['old_price'] != null,
                        'category_parent_slug' => $parentCategory->slug,
                        'category_name' => $category->name,
                        'product' => $variant_price_desc,
                        'created_at' => $variant_price_desc['created_at'],
                        'rating' => $variant_price_desc['rating'],
                        'tags' => $tags,
                        'options_names' => $options['name'],
                        'options_values' => $options['value'],
                        'price' => (int)$variant_price_desc['price'],
                        'brand' => $variant_price_desc['brand'],
                    ]);
                }
            }
            else{
                $category_products =  json_decode($category->load('products')->products, true);
                usort($category_products, function ($a, $b) {
                    return (int)json_decode($a, true)['price'] < (int)json_decode($b, true)['price'];
                });

                foreach ($category_products as $product_price_desc){
                    unset($product_price_desc['min_price']);
                    unset($product_price_desc['min_balance']);
                    unset($product_price_desc['purchase_price']);
                    unset($product_price_desc['vat']);
                    unset($product_price_desc['my_warehouse_id']);
                    unset($product_price_desc['images']);
                    unset($product_price_desc['tags']);
                    unset($product_price_desc['variants']);
                    unset($product_price_desc['baskets']);
                    unset($product_price_desc['country']);
                    unset($product_price_desc['enter']);
                    unset($product_price_desc['property']);
                    unset($product_price_desc['exported']);
                    unset($product_price_desc['category_id']);
                    unset($product_price_desc['brand_id']);
                    $product_price_desc['category'] = $product_price_desc['category']['name'];
                    $product_price_desc['brand'] = $product_price_desc['brand']['name'];
                    $product_price_desc['quantity'] = 1;
                    $client->index('category_variant_price_desc')->updateDocuments([
                        'id' => $product_price_desc['id'],
                        'category_slug' => $category->slug,
                        'in_stock' => $product_price_desc['units_in_stock'] != 0,
                        'with_old_price' => $product_price_desc['old_price'] != null,
                        'category_parent_slug' => $parentCategory->slug,
                        'category_name' => $product_price_desc['category'],
                        'product' => $product_price_desc,
                        'created_at' => $product_price_desc['created_at'],
                        'rating' => $product_price_desc['rating'],
                        'tags' => $tags,
                        'price' => (int)$product_price_desc['price'],
                        'brand' => $product_price_desc['brand'],
                    ]);
                }
            }
        }

        $client->index('category_variant_price_desc')->updateFilterableAttributes([
            'brand',
            'price',
            'in_stock',
            'options_names',
            'with_old_price',
            'category_name',
            'options_values',
            'tags',
        ]);
    }
}
