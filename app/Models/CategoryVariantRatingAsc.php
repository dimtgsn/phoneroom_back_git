<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Meilisearch\Client;

class CategoryVariantRatingAsc extends Model
{
    use HasFactory, Searchable;

    protected $table = 'categories';

    public function searchableAs()
    {
        return 'category_variant_rating_asc';
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



        $category_variants_json =  json_decode($category->load('variants')->variants->pluck('variants_json'), true);
        $category_variants_product_id =  json_decode($category->load('variants')->variants->pluck('product_id'), true);
        usort($category_variants_json, function ($a, $b) {
            return json_decode($a, true)['rating'] > json_decode($b, true)['rating'];
        });

        foreach ($category_variants_json as $variant_rating_asc){
            $option = json_decode($variant_rating_asc, true)['options'];
            $options['name'] = [];
            $options['value'] = [];
            foreach ($option as $name => $val){
                $options['name'][] = $name;
                $options['value'][$name] = $val;
            }
            $client->index('category_variant_rating_asc')->updateDocuments([
                'id' => json_decode($variant_rating_asc, true)['id'],
                'category_slug' => $category->slug,
                'in_stock' => json_decode($variant_rating_asc, true)['units_in_stock'] != 0,
                'category_parent_slug' => $parentCategory->slug,
                'product' => json_decode($variant_rating_asc, true),
                'created_at' => json_decode($variant_rating_asc, true)['created_at'],
                'rating' => json_decode($variant_rating_asc, true)['rating'],
                'tags' => $tags,
                'options_names' => $options['name'],
                'options_values' => $options['value'],
                'price' => json_decode($variant_rating_asc, true)['price'],
                'brand' => json_decode($variant_rating_asc, true)['brand'],
            ]);
            }
        $category_products =  json_decode($category->load('products')->products, true);
        usort($category_products, function ($a, $b) {
            return json_decode($a, true)['rating'] > json_decode($b, true)['rating'];
        });

        foreach ($category_products as $product_rating_asc){
            if (!in_array($product_rating_asc['id'], $category_variants_product_id)){
                $client->index('category_variant_rating_asc')->updateDocuments([
                    'id' => $product_rating_asc['id'],
                    'category_slug' => $category->slug,
                    'in_stock' => $product_rating_asc['units_in_stock'] != 0,
                    'category_parent_slug' => $parentCategory->slug,
                    'product' => $product_rating_asc,
                    'created_at' => $product_rating_asc['created_at'],
                    'rating' => $product_rating_asc['rating'],
                    'tags' => $tags,
                    'price' => $product_rating_asc['price'],
                    'brand' => $product_rating_asc['brand'],
                ]);
            }

        }

        $client->index('category_variant_rating_asc')->updateFilterableAttributes([
            'brand',
            'price',
            'in_stock',
            'options_names',
            'options_values',
            'tags',
        ]);
    }
}
