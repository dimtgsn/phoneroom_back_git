<?php

namespace App\Services\MyWarehouse;

use App\Models\BannerImage;
use App\Models\Category;
use App\Models\Enter;
use App\Models\MyWarehouse;
use App\Models\Product;
use App\Models\Variant;
use App\Utilities\ImageConvertToBase64;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Service
{

    public function connect($data)
    {
        return \DB::transaction(function() use ($data) {

            $response = Http::withBasicAuth($data["login"],$data["password"])
            ->post('https://online.moysklad.ru/api/remap/1.2/security/token');
            if (isset(json_decode($response->body(), JSON_UNESCAPED_UNICODE)['access_token'])){
                $token = json_decode($response->body(), JSON_UNESCAPED_UNICODE)['access_token'];
                if (MyWarehouse::all()->first() !== null){
                    MyWarehouse::all()->first()->update([
                        'token' => $token,
                    ]);
                }
                else{
                    MyWarehouse::firstOrCreate([
                        'token' => $token,
                    ]);
                }
                return true;
            }
            else{
                return false;
            }

        });

    }

    public function export($data){
        $myWarehouse = MyWarehouse::select('token')->first();

        foreach ($data['ids'] as $id => $value){
            $product = Product::where('id', $id)->first();
            if ($product->exported === true){
                return false;
            }

            $image_base64 = new ImageConvertToBase64();
            $image_to_jpeg = new ImageConvertToWebp();
            $image_to_jpeg->convert($product->image);
            $image = $image_base64->convert($image_to_jpeg->convert($product->image));
            $product_price = $product->price * 100;
            $responseGroupAll = Http::withToken($myWarehouse->token)
                ->get('https://online.moysklad.ru/api/remap/1.2/entity/productfolder');
            $responseGroup = null;
            $responseGroupParent = null;
            if (empty(json_decode($responseGroupAll->body(), JSON_UNESCAPED_UNICODE)["rows"]) !== true){

                foreach (json_decode($responseGroupAll->body(), JSON_UNESCAPED_UNICODE)['rows'] as $row){
                    foreach ($row as $key => $val) {
                        if ($key === "name" && $val === $product->category->name){
                            $responseGroup = $row;
                            break;
                        }
                        if ($product->category->parent_id &&
                            ($key === "name" && $val ===  Category::where('id', $product->category->parent_id)->first()->name)){
                            $responseGroupParent = $row;
                        }
                    }
                    if ($responseGroup !== null){
                        break;
                    }
                }
                if ($responseGroup === null){
                    if ($product->category->parent_id){
                        if ($responseGroupParent === null){
                            //create category(parent)
                            $categoryParent = Category::where('id', $product->category->parent_id)->first();
                            $responseGroupParent = Http::withToken($myWarehouse->token)
                                ->withHeaders([
                                    "Content-Type" => "application/json"
                                ])
                                ->post('https://online.moysklad.ru/api/remap/1.2/entity/productfolder', [
                                    "name" => $categoryParent->name,
                                ]);
                            Category::where('id', $product->category->parent_id)->update([
                                "my_warehouse_id" => json_decode($responseGroupParent->body(), JSON_UNESCAPED_UNICODE)['id']
                            ]);
                        }

                        // creating goods group
                        $responseGroup = Http::withToken($myWarehouse->token)
                            ->withHeaders([
                                "Content-Type" => "application/json"
                            ])
                            ->post('https://online.moysklad.ru/api/remap/1.2/entity/productfolder', [
                                "name" => $product->category->name,
                                "productFolder" => [
                                    'meta' => $responseGroupParent['meta']
                                        ?? json_decode($responseGroupParent->body(), JSON_UNESCAPED_UNICODE)["meta"],
                                ],
                            ]);
                        $product->category->update([
                            "my_warehouse_id" => json_decode($responseGroup->body(), JSON_UNESCAPED_UNICODE)['id']
                        ]);
                    }
                    else{
                        // creating goods group
                        $responseGroup = Http::withToken($myWarehouse->token)
                            ->withHeaders([
                                "Content-Type" => "application/json"
                            ])
                            ->post('https://online.moysklad.ru/api/remap/1.2/entity/productfolder', [
                                "name" => $product->category->name,
                            ]);
                        $product->category->update([
                            "my_warehouse_id" => json_decode($responseGroup->body(), JSON_UNESCAPED_UNICODE)['id']
                        ]);
                    }
                }
            }
            else{
                //create category(parent)
                if ($product->category->parent_id){
                    $categoryParent = Category::where('id', $product->category->parent_id)->first();
                    $responseGroupParent = Http::withToken($myWarehouse->token)
                        ->withHeaders([
                            "Content-Type" => "application/json"
                        ])
                        ->post('https://online.moysklad.ru/api/remap/1.2/entity/productfolder', [
                            "name" => $categoryParent->name,
                        ]);
                    Category::where('id', $product->category->parent_id)->update([
                        "my_warehouse_id" => json_decode($responseGroupParent->body(), JSON_UNESCAPED_UNICODE)['id']
                    ]);
                    // creating goods group
                    $responseGroup = Http::withToken($myWarehouse->token)
                        ->withHeaders([
                            "Content-Type" => "application/json"
                        ])
                        ->post('https://online.moysklad.ru/api/remap/1.2/entity/productfolder', [
                            "name" => $product->category->name,
                            "productFolder" => [
                                'meta' => json_decode($responseGroupParent->body(), JSON_UNESCAPED_UNICODE)["meta"],
                            ],
                        ]);
                    $product->category->update([
                        "my_warehouse_id" => json_decode($responseGroup->body(), JSON_UNESCAPED_UNICODE)['id']
                    ]);
                }
                else{
                    // creating goods group
                    $responseGroup = Http::withToken($myWarehouse->token)
                        ->withHeaders([
                            "Content-Type" => "application/json"
                        ])
                        ->post('https://online.moysklad.ru/api/remap/1.2/entity/productfolder', [
                            "name" => $product->category->name,
                        ]);
                    $product->category->update([
                        "my_warehouse_id" => json_decode($responseGroup->body(), JSON_UNESCAPED_UNICODE)['id']
                    ]);
                }
            }
            //get default price type
            $responsePriceType = Http::withToken($myWarehouse->token)
                ->get('https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/default');

            $responseProduct = Http::withToken($myWarehouse->token)
                ->withHeaders([
                    "Content-Type" => "application/json"
                ])
                ->post('https://online.moysklad.ru/api/remap/1.2/entity/product', [
                    "name" => $product->name,
                    "code" => (string)$product->id,
                    "article" => (string)$product->sku,
                    "vat" => $product->vat === -1 ? 0:$product->vat,
                    "vatEnabled" => !($product->vat === -1),
                    "productFolder" => [
                        "meta" =>  $responseGroup['meta'] ??
                            json_decode($responseGroup->body(), JSON_UNESCAPED_UNICODE)["meta"],
                    ],
                    "description" => $product->description,
                    "salePrices" => [
                        [
                            'value' => $product_price,
                            "priceType" => [
                                "meta" => json_decode($responsePriceType->body(), JSON_UNESCAPED_UNICODE)['meta'],
                            ],
                        ],
                    ],
                    "minPrice" => [
                        "value" => $product->min_price * 100,
                    ],
                    "buyPrice" => [
                        "value" => $product->purchase_price * 100,
                    ],
                    'minimumBalance' => $product->min_balance,
                    "images" => [
                        [
                            "filename" => $product->slug,
                            "content" => $image,
                        ],
                    ],
                ]);
//            dd(json_decode($responseProduct->body(), JSON_UNESCAPED_UNICODE));
            $product->update([
                'my_warehouse_id' => json_decode($responseProduct->body(), JSON_UNESCAPED_UNICODE)['id']
            ]);
            //units in stock
            $org = Http::withToken($myWarehouse->token)
                ->get('https://online.moysklad.ru/api/remap/1.2/entity/organization');

            $warehouse = Http::withToken($myWarehouse->token)
                ->get('https://online.moysklad.ru/api/remap/1.2/entity/store');
            if (empty(json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE)["rows"]) === true){
                $warehouse = Http::withToken($myWarehouse->token)
                    ->withHeaders([
                        "Content-Type" => "application/json"
                    ])
                    ->post('https://online.moysklad.ru/api/remap/1.2/entity/store', [
                        "name" => "Основной склад",
                    ]);
            }

            if ($product->option){
                //create characteristics
                $responseVariantsAll = Http::withToken($myWarehouse->token)
                    ->get("https://online.moysklad.ru/api/remap/1.2/entity/variant/metadata");
                foreach (json_decode($product->option->options_json, JSON_UNESCAPED_UNICODE) as $option) {
                    if (isset(json_decode($responseVariantsAll->body(), JSON_UNESCAPED_UNICODE)['characteristics'])) {
                        foreach (json_decode($responseVariantsAll->body(), JSON_UNESCAPED_UNICODE)['characteristics'] as $row => $variant){
                            if($variant['name'] === $option["name"]){
                                break;
                            }
                            if ($row === count(json_decode($responseVariantsAll->body(), JSON_UNESCAPED_UNICODE)['characteristics']) - 1){
                                Http::withToken($myWarehouse->token)
                                    ->withHeaders([
                                        "Content-Type" => "application/json"
                                    ])
                                    ->post("https://online.moysklad.ru/api/remap/1.2/entity/variant/metadata/characteristics", [
                                        "name" => $option["name"],
                                    ]);
                            }
                        }
                    }
                    else{
                        Http::withToken($myWarehouse->token)
                            ->withHeaders([
                                "Content-Type" => "application/json"
                            ])
                            ->post("https://online.moysklad.ru/api/remap/1.2/entity/variant/metadata/characteristics", [
                                "name" => $option["name"],
                            ]);
                    }
                }

                //create variants
                $variants_edit = Variant::where('product_id', $product->id)->get();

                foreach ($product->variants as $i => $variants){
                    $variant = json_decode($variants->variants_json, JSON_UNESCAPED_UNICODE);
                    $characteristics = [];
                    foreach ($variant['options'] as $key => $val){
                        $characteristics[] = [
                            "name" => $key,
                            "value" => $val,
                        ];
                    }

                    $responseProductVariant = Http::withToken($myWarehouse->token)
                        ->withHeaders([
                            "Content-Type" => "application/json"
                        ])
                        ->post('https://online.moysklad.ru/api/remap/1.2/entity/variant', [
                            "name" => $variant['name'],
                            'characteristics' => $characteristics,
                            "code" => (string)$variant['id'],
                            "article" => $variant['sku'],
                            "vat" => $product->vat === -1 ? 0:$product->vat,
                            "vatEnabled" => !($product->vat === -1),
                            "description" => $variant['description'],
                            "salePrices" => [
                                [
                                    'value' => $variant['price'] * 100,
                                    "priceType" => [
                                        "meta" => json_decode($responsePriceType->body(), JSON_UNESCAPED_UNICODE)['meta'],
                                    ],
                                ],
                            ],
                            "minPrice" => [
                                "value" => $variant['min_price'] * 100,
                            ],
                            "buyPrice" => [
                                "value" => $variant['purchase_price'] * 100,
                            ],
                            'minimumBalance' => $variant['min_balance'],
                            "product" => [
                                "meta" => json_decode($responseProduct->body(), JSON_UNESCAPED_UNICODE)['meta'],
                            ],
                        ]);
                    $variant['my_warehouse_id'] = json_decode($responseProductVariant->body(), JSON_UNESCAPED_UNICODE)['id'];
                    $variants_edit[$i]->update([
                        'variants_json' => json_encode($variant, JSON_UNESCAPED_UNICODE),
                    ]);

                    if ((int)$variant['units_in_stock'] > 0){
                        $responseEnter = Http::withToken($myWarehouse->token)
                            ->withHeaders([
                                "Content-Type" => "application/json"
                            ])
                            ->post('https://online.moysklad.ru/api/remap/1.2/entity/enter', [
                                "name" => "enter".$variant['id'],
                                "organization" => [
                                    "meta" => json_decode($org->body(), JSON_UNESCAPED_UNICODE)['rows'][0]['meta']
                                ],
                                "store" => [
                                    "meta" => json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE)['rows'][0]['meta'] ??
                                        json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE)['meta']
                                ],
                                "positions" => [
                                    [
                                        "quantity" => (float)$variant['units_in_stock'],
                                        "price" => $variant['price'] * 100,
                                        "assortment" => [
                                            "meta" => json_decode($responseProductVariant->body(), JSON_UNESCAPED_UNICODE)['meta']
                                        ]
                                    ]
                                ],
                            ]);
                        Enter::firstOrCreate([
                            'enter_id' => json_decode($responseEnter->body(), JSON_UNESCAPED_UNICODE)['id'],
                            'product_id' => $product->id,
                            'variant_id' => $variant['id'],
                        ]);
                    }

                }
            }
            else{
                if ((int)$product->units_in_stock > 0){
                    $responseEnter = Http::withToken($myWarehouse->token)
                        ->withHeaders([
                            "Content-Type" => "application/json"
                        ])
                        ->post('https://online.moysklad.ru/api/remap/1.2/entity/enter', [
                            "name" => "enter".$product->id,
                            "organization" => [
                                "meta" => json_decode($org->body(), JSON_UNESCAPED_UNICODE)['rows'][0]['meta']
                            ],
                            "store" => [
                                "meta" => json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE)['rows'][0]['meta'] ??
                                    json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE)['meta']
                            ],
                            "positions" => [
                                [
                                    "quantity" => $product->units_in_stock,
                                    "price" => $product_price,
                                    "assortment" => [
                                        "meta" => json_decode($responseProduct->body(), JSON_UNESCAPED_UNICODE)['meta']
                                    ]
                                ]
                            ],
                        ]);
                    Enter::firstOrCreate([
                        'enter_id' => json_decode($responseEnter->body(), JSON_UNESCAPED_UNICODE)['id'],
                        'product_id' => $product->id
                    ]);
                }
            }

            $product->update([
                'exported' => true,
            ]);
        }

        return true;
    }
}