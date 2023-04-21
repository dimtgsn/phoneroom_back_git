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
                    return json_decode($a, true)['rating'] < json_decode($b, true)['rating'];
                });
                foreach ($category_variants_json as $variant_rating_desc){
                    $option = json_decode($variant_rating_desc, true)['options'];
                    $options['name'] = [];
                    $options['value'] = [];
                    foreach ($option as $name => $val){
                        $options['name'][] = $name;
                        $name = str_replace(" ", '', $name);
                        $name = preg_replace('/[^ a-zа-яё\d]/ui', '',$name );
                        $options['value'] += array($translation->translate($name) => $val);
                    }
                    $client->index('category_variant_rating_desc')->updateDocuments([
                        'id' => json_decode($variant_rating_desc, true)['id'],
                        'category_slug' => $category->slug,
                        'in_stock' => json_decode($variant_rating_desc, true)['units_in_stock'] != 0,
                        'with_old_price' => json_decode($variant_rating_desc, true)['old_price'] != null,
                        'category_parent_slug' => $parentCategory->slug,
                        'product' => json_decode($variant_rating_desc, true),
                        'category_name' => $category->name,
                        'created_at' => json_decode($variant_rating_desc, true)['created_at'],
                        'rating' => json_decode($variant_rating_desc, true)['rating'],
                        'tags' => $tags,
                        'options_names' => $options['name'],
                        'options_values' => $options['value'],
                        'price' => (int)json_decode($variant_rating_desc, true)['price'],
                        'brand' => json_decode($variant_rating_desc, true)['brand'],
                    ]);
                }

            }
            else{
                $category_products =  json_decode($category->load('products')->products, true);
                usort($category_products, function ($a, $b) {
                    return json_decode($a, true)['rating'] < json_decode($b, true)['rating'];
                });

                foreach ($category_products as $product_rating_desc){
//                    if (!in_array($product_rating_desc['id'], $category_variants_product_id)){
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
//                    }
                }
            }
        }

//        $category_variants_product_id = json_decode($category->load('variants')->variants->pluck('product_id'), true);

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
//
//        $client->index('category_variant_rating_desc')->search('', [
//            'filter' => 'options_values IN [128ГБ, 256ГБ, 512ГБ]'
//        ]);
    }
}
