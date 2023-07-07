<?php

namespace App\Models;

use App\Utilities\TranslationIntoLatin;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Meilisearch\Client;

class CategoryVariantPriceAsc extends Model
{
    use HasFactory, Searchable;

    protected $table = 'categories';

    public function searchableAs()
    {
        return 'category_variant_price_asc';
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
                    return (int)json_decode($a, true)['price'] - (int)json_decode($b, true)['price'];
                });
                foreach ($category_variants_json as $variant_price_asc){
                    $option = json_decode($variant_price_asc, true)['options'];

                    $variant_price_asc = json_decode($variant_price_asc, true);
                    unset($variant_price_asc['min_price']);
                    unset($variant_price_asc['min_balance']);
                    unset($variant_price_asc['purchase_price']);
                    unset($variant_price_asc['country']);
                    $variant_price_asc['quantity'] = 1;

                    $options['name'] = [];
                    $options['value'] = [];
                    foreach ($option as $name => $val){
                        $options['name'][] = $name;
                        $name = str_replace(" ", '', $name);
                        $name = preg_replace('/[^ a-zа-яё\d]/ui', '',$name );
                        $options['value'] += array(TranslationIntoLatin::translate($name) => $val);
                    }
                    $client->index('category_variant_price_asc')->updateDocuments([
                        'id' => $variant_price_asc['id'],
                        'category_slug' => $category->slug,
                        'in_stock' => $variant_price_asc['units_in_stock'] != 0,
                        'with_old_price' => $variant_price_asc['old_price'] != null,
                        'category_parent_slug' => $parentCategory->slug,
                        'category_name' => $category->name,
                        'product' => $variant_price_asc,
                        'created_at' => $variant_price_asc['created_at'],
                        'rating' => $variant_price_asc['rating'],
                        'tags' => $tags,
                        'options_names' => $options['name'],
                        'options_values' => $options['value'],
                        'price' => (int)$variant_price_asc['price'],
                        'brand' => $variant_price_asc['brand'],
                    ]);
                }
            }
            else{
                $category_products =  json_decode($category->load('products')->products, true);
                usort($category_products, function ($a, $b) {
                    return (int)json_decode($a, true)['price'] - (int)json_decode($b, true)['price'];
                });

                foreach ($category_products as $product_price_asc){
                    unset($product_price_asc['min_price']);
                    unset($product_price_asc['min_balance']);
                    unset($product_price_asc['purchase_price']);
                    unset($product_price_asc['vat']);
                    unset($product_price_asc['my_warehouse_id']);
                    unset($product_price_asc['images']);
                    unset($product_price_asc['tags']);
                    unset($product_price_asc['variants']);
                    unset($product_price_asc['baskets']);
                    unset($product_price_asc['country']);
                    unset($product_price_asc['enter']);
                    unset($product_price_asc['property']);
                    unset($product_price_asc['exported']);
                    unset($product_price_asc['category_id']);
                    unset($product_price_asc['brand_id']);
                    $product_price_asc['category'] = $product_price_asc['category']['name'];
                    $product_price_asc['brand'] = $product_price_asc['brand']['name'];
                    $product_price_asc['quantity'] = 1;
                    $client->index('category_variant_price_asc')->updateDocuments([
                        'id' => $product_price_asc['id'],
                        'category_slug' => $category->slug,
                        'in_stock' => $product_price_asc['units_in_stock'] != 0,
                        'with_old_price' => $product_price_asc['old_price'] != null,
                        'category_parent_slug' => $parentCategory->slug,
                        'category_name' => $product_price_asc['category'],
                        'product' => $product_price_asc,
                        'created_at' => $product_price_asc['created_at'],
                        'rating' => $product_price_asc['rating'],
                        'tags' => $tags,
                        'price' => (int)$product_price_asc['price'],
                        'brand' => $product_price_asc['brand'],
                    ]);
                }
            }
        }

        $client->index('category_variant_price_asc')->updateFilterableAttributes([
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
