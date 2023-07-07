<?php

namespace App\Models;

use App\Utilities\TranslationIntoLatin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Meilisearch\Client;

class CategoryVariantRatingDesc extends Model
{
    use HasFactory, Searchable;

    protected $table = 'categories';

    public function searchableAs()
    {
        return 'category_variant_rating_desc';
    }

    public function toSearchableArray()
    {
        $client = new Client('http://127.0.0.1:7700');

        $category = Category::find($this->id);

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
                    return json_decode($a, true)['rating'] < json_decode($b, true)['rating'];
                });
                foreach ($category_variants_json as $variant_rating_desc){
                    $option = json_decode($variant_rating_desc, true)['options'];

                    $variant_rating_desc = json_decode($variant_rating_desc, true);
                    unset($variant_rating_desc['min_price']);
                    unset($variant_rating_desc['min_balance']);
                    unset($variant_rating_desc['purchase_price']);
                    unset($variant_rating_desc['country']);
                    $variant_rating_desc['quantity'] = 1;

                    $options['name'] = [];
                    $options['value'] = [];
                    foreach ($option as $name => $val){
                        $options['name'][] = $name;
                        $name = str_replace(" ", '', $name);
                        $name = preg_replace('/[^ a-zа-яё\d]/ui', '',$name );
                        $options['value'] += array(TranslationIntoLatin::translate($name) => $val);
                    }
                    $client->index('category_variant_rating_desc')->updateDocuments([
                        'id' => $variant_rating_desc['id'],
                        'category_slug' => $category->slug,
                        'in_stock' => $variant_rating_desc['units_in_stock'] != 0,
                        'with_old_price' => $variant_rating_desc['old_price'] != null,
                        'category_parent_slug' => $parentCategory->slug,
                        'product' => $variant_rating_desc,
                        'category_name' => $category->name,
                        'created_at' => $variant_rating_desc['created_at'],
                        'rating' => $variant_rating_desc['rating'],
                        'tags' => $tags,
                        'options_names' => $options['name'],
                        'options_values' => $options['value'],
                        'price' => (int)$variant_rating_desc['price'],
                        'brand' => $variant_rating_desc['brand'],
                    ]);
                }

            }
            else{
                $category_products =  json_decode($category->load('products')->products, true);
                usort($category_products, function ($a, $b) {
                    return json_decode($a, true)['rating'] < json_decode($b, true)['rating'];
                });

                foreach ($category_products as $product_rating_desc){
                    unset($product_rating_desc['min_price']);
                    unset($product_rating_desc['min_balance']);
                    unset($product_rating_desc['purchase_price']);
                    unset($product_rating_desc['vat']);
                    unset($product_rating_desc['my_warehouse_id']);
                    unset($product_rating_desc['images']);
                    unset($product_rating_desc['tags']);
                    unset($product_rating_desc['variants']);
                    unset($product_rating_desc['baskets']);
                    unset($product_rating_desc['country']);
                    unset($product_rating_desc['enter']);
                    unset($product_rating_desc['property']);
                    unset($product_rating_desc['exported']);
                    unset($product_rating_desc['category_id']);
                    unset($product_rating_desc['brand_id']);
                    $product_rating_desc['category'] = $product_rating_desc['category']['name'];
                    $product_rating_desc['brand'] = $product_rating_desc['brand']['name'];
                    $product_rating_desc['quantity'] = 1;
                    $client->index('category_variant_rating_desc')->updateDocuments([
                        'id' => $product_rating_desc['id'],
                        'category_slug' => $category->slug,
                        'in_stock' => $product_rating_desc['units_in_stock'] != 0,
                        'with_old_price' => $product_rating_desc['old_price'] != null,
                        'category_parent_slug' => $parentCategory->slug,
                        'product' => $product_rating_desc,
                        'category_name' => $product_rating_desc['category'],
                        'created_at' => $product_rating_desc['created_at'],
                        'rating' => $product_rating_desc['rating'],
                        'tags' => $tags,
                        'price' => (int)$product_rating_desc['price'],
                        'brand' => $product_rating_desc['brand'],
                    ]);
                }
            }
        }

        $client->index('category_variant_rating_desc')->updateFilterableAttributes([
            'brand',
            'price',
            'in_stock',
            'with_old_price',
            'category_name',
            'options_names',
            'options_values',
            'tags',
        ]);
    }

}
