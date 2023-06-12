<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Meilisearch\Client;

class Variant extends Model
{
    use HasFactory;
    protected $table = 'variants';

//    public function shouldBeSearchable()
//    {
//        return json_decode($this->variants_json,true)['published'] === true;
//    }
//
//    public function toSearchableArray()
//    {
//        $client = new Client('http://127.0.0.1:7700');
//        $client->index('variants')->updateFilterableAttributes([
//                'variants_json.price',
//                'variants_json.rating',
//                'variants_json.product_name',
//                'variants_json.brand',
//                'variants_json.options',
//            ]);
//        return [
//            'product_id' => $this->product_id,
//            'variants_json' => json_decode($this->variants_json,true),
//        ];
//    }

    protected $fillable = [
        'product_id',
        'variants_json',
    ];

    protected $casts = [
        'variants_json' => 'array'
    ];


}
