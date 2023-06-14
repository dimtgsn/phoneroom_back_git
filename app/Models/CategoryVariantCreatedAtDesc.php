<?php

namespace App\Models;

use App\Utilities\TranslationIntoLatin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Meilisearch\Client;

class CategoryVariantCreatedAtDesc extends Model
{
    use HasFactory, Searchable;

    protected $table = 'categories';

    public function searchableAs()
    {
        return 'category_variant_created_at_desc';
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
                    return json_decode($b, true)['id'] - json_decode($a, true)['id'];
                });
                foreach ($category_variants_json as $variant_created_at_desc){
                    $option = json_decode($variant_created_at_desc, true)['options'];

                    $variant_created_at_desc = json_decode($variant_created_at_desc, true);
                    unset($variant_created_at_desc['min_price']);
                    unset($variant_created_at_desc['min_balance']);
                    unset($variant_created_at_desc['purchase_price']);
                    unset($variant_created_at_desc['country']);
                    $variant_created_at_desc['quantity'] = 1;

                    $options['name'] = [];
                    $options['value'] = [];
                    foreach ($option as $name => $val){
                        $options['name'][] = $name;
                        $name = str_replace(" ", '', $name);
                        $name = preg_replace('/[^ a-zа-яё\d]/ui', '',$name );
                        $options['value'] += array($translation->translate($name) => $val);
                    }
                    $client->index('category_variant_created_at_desc')->updateDocuments([
                        'id' => $variant_created_at_desc['id'],
                        'category_slug' => $category->slug,
                        'in_stock' => $variant_created_at_desc['units_in_stock'] != 0,
                        'with_old_price' => $variant_created_at_desc['old_price'] != null,
                        'category_parent_slug' => $parentCategory->slug,
                        'product' => $variant_created_at_desc,
                        'category_name' => $category->name,
                        'created_at' => $variant_created_at_desc['created_at'],
                        'rating' => $variant_created_at_desc['rating'],
                        'tags' => $tags,
                        'options_names' => $options['name'],
                        'options_values' => $options['value'],
                        'price' => (int)$variant_created_at_desc['price'],
                        'brand' => $variant_created_at_desc['brand'],
                    ]);
                }
            }
            else{
                $category_products =  json_decode($category->load('products')->products, true);
                usort($category_products, function ($a, $b) {
                    return json_decode($a, true)['created_at'] <=> json_decode($b, true)['created_at'];
                });

                foreach ($category_products as $product_created_at_desc){
                    unset($product_created_at_desc['min_price']);
                    unset($product_created_at_desc['min_balance']);
                    unset($product_created_at_desc['purchase_price']);
                    unset($product_created_at_desc['vat']);
                    unset($product_created_at_desc['my_warehouse_id']);
                    unset($product_created_at_desc['images']);
                    unset($product_created_at_desc['tags']);
                    unset($product_created_at_desc['variants']);
                    unset($product_created_at_desc['baskets']);
                    unset($product_created_at_desc['country']);
                    unset($product_created_at_desc['enter']);
                    unset($product_created_at_desc['property']);
                    unset($product_created_at_desc['exported']);
                    unset($product_created_at_desc['category_id']);
                    unset($product_created_at_desc['brand_id']);
                    $product_created_at_desc['category'] = $product_created_at_desc['category']['name'];
                    $product_created_at_desc['brand'] = $product_created_at_desc['brand']['name'];
                    $product_created_at_desc['quantity'] = 1;
                    $client->index('category_variant_created_at_desc')->updateDocuments([
                        'id' => $product_created_at_desc['id'],
                        'category_slug' => $category->slug,
                        'in_stock' => $product_created_at_desc['units_in_stock'] != 0,
                        'with_old_price' => $product_created_at_desc['old_price'] != null,
                        'category_parent_slug' => $parentCategory->slug,
                        'product' => $product_created_at_desc,
                        'category_name' => $product_created_at_desc['category'],
                        'created_at' => $product_created_at_desc['created_at'],
                        'rating' => $product_created_at_desc['rating'],
                        'tags' => $tags,
                        'price' => (int)$product_created_at_desc['price'],
                        'brand' => $product_created_at_desc['brand'],
                    ]);
                }
            }
        }

        $client->index('category_variant_created_at_desc')->updateFilterableAttributes([
            'brand',
            'price',
            'in_stock',
            'options_names',
            'category_name',
            'with_old_price',
            'options_values',
            'tags',
        ]);
    }
}
