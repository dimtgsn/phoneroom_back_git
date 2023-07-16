<?php

namespace App\Services\MyWarehouse;

use App\Models\BannerImage;
use App\Models\Category;
use App\Models\Enter;
use App\Models\Image;
use App\Models\MyWarehouse;
use App\Models\Product;
use App\Models\Variant;
use App\Utilities\ImageConvertToBase64;
use App\Utilities\ImageConvertToWebp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\Backtrace\Arguments\Reducers\ArrayArgumentReducer;

class Service
{
    protected $organization = [];
    protected $warehouse = [];

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
//        set_time_limit(0);
        foreach ($data['ids'] as $id => $value){
            $product = Product::where('id', $id)->first();
            if ($product->exported === true){
                return false;
            }
            $productImages = [
                [
                    "filename" => $product->slug.'.jpeg',
                    "content" => ImageConvertToBase64::convert(ImageConvertToWebp::convert(asset($product->image)))
                ]
            ];
            $images_without_variants = Image::where('product_id', $product->id)->where('variant_id', null)->get();
            if (count($images_without_variants)){
                foreach ($images_without_variants as $img){
                    if($img->path){
                        $productImages[] = [
                            "filename" => $product->slug.'-thumb-image-'.$img->position.'.jpeg',
                            "content" => ImageConvertToBase64::convert(ImageConvertToWebp::convert(asset($img->path))),
                        ];
                    }
                }
            }
            $product_price = $product->price * 100;
            $group = null;
            $groupParent = null;
            $getGroupsValue = $this->getGroups($myWarehouse);
            if (empty($getGroupsValue["rows"]) !== true){

                foreach ($getGroupsValue['rows'] as $row){
                    foreach ($row as $key => $val) {
                        if ($key === "name" && $val === $product->category->name){
                            $group = $row;
                            break;
                        }
                        if ($product->category->parent_id &&
                            ($key === "name" && $val ===  Category::where('id', $product->category->parent_id)->first()->name)){
                            $groupParent = $row;
                        }
                    }
                    if ($group !== null){
                        break;
                    }
                }
                if ($group === null){
                    if ($product->category->parent_id){
                        $categoryParent = Category::where('id', $product->category->parent_id)->first();
                        if ($groupParent === null){
                            // creating group(parent)
                            $groupParent = $this->createParentGroup($myWarehouse, $categoryParent->name);
                            Category::where('id', $product->category->parent_id)->update([
                                "my_warehouse_id" => $groupParent['id']
                            ]);
                        }

                        // creating group
                        $group = $this->createGroup($myWarehouse, $product->category->name, $groupParent);
                        $product->category->update([
                            "my_warehouse_id" => $group['id']
                        ]);
                    }
                    else{
                        // creating group
                        $group = $this->createGroup($myWarehouse, $product->category->name);
                        $product->category->update([
                            "my_warehouse_id" => $group['id']
                        ]);
                    }
                }
            }
            else{
                // creating group(parent)
                if ($product->category->parent_id){
                    $categoryParent = Category::where('id', $product->category->parent_id)->first();
                    $groupParent = $this->createParentGroup($myWarehouse, $categoryParent->name);
                    Category::where('id', $product->category->parent_id)->update([
                        "my_warehouse_id" => $groupParent['id']
                    ]);
                    // creating goods group
                    $group = $this->createGroup($myWarehouse, $product->category->name, $groupParent);
                    $product->category->update([
                        "my_warehouse_id" => $group['id']
                    ]);
                }
                else{
                    // creating goods group
                    $group = $this->createGroup($myWarehouse, $product->category->name);
                    $product->category->update([
                        "my_warehouse_id" => $group['id']
                    ]);
                }
            }
            $getPriceTypeValue = $this->getPriceType($myWarehouse);
            $this->organization = $this->getOrganization($myWarehouse);
            $this->warehouse = $this->getWarehouse($myWarehouse);
            $newProduct = $this->createProduct($myWarehouse, $product, $productImages, $group, $product_price, $getPriceTypeValue);
            $product->update([
                'my_warehouse_id' => $newProduct['id']
            ]);

            if ($product->option){
                // create characteristics
                $characteristics = $this->getCharacteristics($myWarehouse);
                foreach (json_decode($product->option->options_json, JSON_UNESCAPED_UNICODE) as $option) {
                    if (isset($characteristics['characteristics'])) {
                        foreach ($characteristics['characteristics'] as $row => $variant){
                            if($variant['name'] === $option["name"]){
                                break;
                            }
                            if ($row === count($characteristics['characteristics']) - 1){
                                $this->createCharacteristic($myWarehouse, $option["name"]);
                            }
                        }
                    }
                    else{
                        $this->createCharacteristic($myWarehouse, $option["name"]);
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
                    $variantImages = [
                        [
                            "filename" => $product->slug.'.jpeg',
                            "content" => ImageConvertToBase64::convert(ImageConvertToWebp::convert(asset($variant['image']))),
                        ]
                    ];
                    $images_with_variants = Image::where('product_id', $product->id)->where('variant_id', (int)$variant['id'])->get();
                    if (count($images_with_variants)){
                        foreach ($images_with_variants as $img){
                            if ($img->path){
                                $variantImages[] = [
                                    "filename" => $variant['slug'].'-thumb-image-'.$img->position.'.jpeg',
                                    "content" => ImageConvertToBase64::convert(ImageConvertToWebp::convert(asset($img->path))),
                                ];
                            }
                        }
                    }
                    $newProductVariant = $this->createProductVariant($myWarehouse, $product, $variant, $variantImages, $characteristics, $newProduct, $getPriceTypeValue);
                    $variant['my_warehouse_id'] = $newProductVariant['id'];
                    $variants_edit[$i]->update([
                        'variants_json' => json_encode($variant, JSON_UNESCAPED_UNICODE),
                    ]);

                    if ((int)$variant['units_in_stock'] > 0){
                        $variant_price = $variant['price'] * 100;
                        $enter = $this->createEnter($myWarehouse, $variant, $variant_price, $newProductVariant);
                        Enter::firstOrCreate([
                            'enter_id' => $enter['id'],
                            'product_id' => $product->id,
                            'variant_id' => $variant['id'],
                        ]);
                    }

                }
            }
            else{
                if ((int)$product->units_in_stock > 0){
                    $enter = $this->createEnter($myWarehouse, $product, $product_price, $newProduct);
                    Enter::firstOrCreate([
                        'enter_id' => $enter['id'],
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

    public function getCurlOptArray($url, $method, $myWarehouse, $data='{}'){
        return array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 500,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Bearer ".$myWarehouse->token,
            ),
        );
    }

    public function getOrganization($myWarehouse){
//        $org = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/organization');
//        return json_decode($org->body(), JSON_UNESCAPED_UNICODE);
        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/organization',
            'GET',
            $myWarehouse
        ));
        $org = curl_exec($curl);
        curl_close($curl);
        return json_decode($org, JSON_UNESCAPED_UNICODE);
    }

    public function getWarehouse($myWarehouse){
//        $warehouse = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/store');
//        if (empty(json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE)["rows"]) === true){
//            $warehouse = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//                ->withHeaders([
//                    "Content-Type" => "application/json"
//                ])
//                ->post('https://online.moysklad.ru/api/remap/1.2/entity/store', [
//                    "name" => "Основной склад",
//                ]);
//        }
//
//        return json_decode($warehouse->body(), JSON_UNESCAPED_UNICODE);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/store',
            'GET',
            $myWarehouse
        ));
        $warehouse = curl_exec($curl);
        curl_close($curl);
        if (empty(json_decode($warehouse, JSON_UNESCAPED_UNICODE)["rows"]) === true){
            $curl = curl_init();
            curl_setopt_array($curl, $this->getCurlOptArray(
                'https://online.moysklad.ru/api/remap/1.2/entity/store',
                'POST',
                $myWarehouse,
                json_encode([
                    "name" => "Основной склад",
                ], true)
            ));
            $warehouse = curl_exec($curl);
            curl_close($curl);
        }
        return json_decode($warehouse, JSON_UNESCAPED_UNICODE);
    }

    public function getGroups($myWarehouse){
//        $groups = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/productfolder');
//
//        return json_decode($groups->body(), JSON_UNESCAPED_UNICODE);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/productfolder',
            'GET',
            $myWarehouse
        ));
        $groups = curl_exec($curl);
        curl_close($curl);
        return json_decode($groups, JSON_UNESCAPED_UNICODE);
    }

    public function createGroup($myWarehouse, $categoryName, $groupParent=null){
//        if($groupParent === null){
//            $group = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//                ->withHeaders([
//                    "Content-Type" => "application/json"
//                ])
//                ->post('https://online.moysklad.ru/api/remap/1.2/entity/productfolder', [
//                    "name" => $categoryName,
//                ]);
//        }
//        else{
//            $group = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//                ->withHeaders([
//                    "Content-Type" => "application/json"
//                ])
//                ->post('https://online.moysklad.ru/api/remap/1.2/entity/productfolder', [
//                    "name" => $categoryName,
//                    "productFolder" => [
//                        'meta' => $groupParent['meta'],
//                    ],
//                ]);
//        }
//        return json_decode($group->body(), JSON_UNESCAPED_UNICODE);
        if($groupParent === null){
            $curl = curl_init();
            curl_setopt_array($curl, $this->getCurlOptArray(
                'https://online.moysklad.ru/api/remap/1.2/entity/productfolder',
                'POST',
                $myWarehouse,
                json_encode([
                    "name" => $categoryName,
                ], true)
            ));
            $group = curl_exec($curl);
            curl_close($curl);
        }
        else{
            $curl = curl_init();
            curl_setopt_array($curl, $this->getCurlOptArray(
                'https://online.moysklad.ru/api/remap/1.2/entity/productfolder',
                'POST',
                $myWarehouse,
                json_encode([
                    "name" => $categoryName,
                    "productFolder" => [
                        'meta' => $groupParent['meta'],
                    ],
                ], true)
            ));
            $group = curl_exec($curl);
            curl_close($curl);
        }

        return json_decode($group, JSON_UNESCAPED_UNICODE);
    }

    public function createParentGroup($myWarehouse, $categoryParentName){
//        $groupParent = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->withHeaders([
//                "Content-Type" => "application/json"
//            ])
//            ->post('https://online.moysklad.ru/api/remap/1.2/entity/productfolder', [
//                "name" => $categoryParentName,
//            ]);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/productfolder',
            'POST',
            $myWarehouse,
            json_encode(
                [
                    "name" => $categoryParentName
                ], true),
        ));
        $groupParent = curl_exec($curl);
        curl_close($curl);
        return json_decode($groupParent, JSON_UNESCAPED_UNICODE);
    }

    public function getPriceType($myWarehouse){
//        $priceType = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/default');
//
//        return json_decode($priceType->body(), JSON_UNESCAPED_UNICODE);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype/default',
            'GET',
            $myWarehouse,
        ));
        $priceType = curl_exec($curl);
        curl_close($curl);
        return json_decode($priceType, JSON_UNESCAPED_UNICODE);
    }

    public function createProduct($myWarehouse, $product, $productImages, $group, $product_price, $getPriceTypeValue){
//        $newProduct = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->withHeaders([
//                "Content-Type" => "application/json"
//            ])
//            ->post('https://online.moysklad.ru/api/remap/1.2/entity/product', [
//                "name" => $product->name,
//                "code" => (string)$product->id,
//                "article" => (string)$product->sku,
//                "vat" => $product->vat === -1 ? 0:$product->vat,
//                "vatEnabled" => !($product->vat === -1),
//                "productFolder" => [
//                    "meta" =>  $group['meta'],
//                ],
//                "description" => $product->description,
//                "salePrices" => [
//                    [
//                        'value' => $product_price,
//                        "priceType" => [
//                            "meta" => $getPriceTypeValue['meta'],
//                        ],
//                    ],
//                ],
//                "uom" => [
//                    'meta' => $this->getUom($myWarehouse)['rows'][0]['meta'],
//                ],
//                "minPrice" => [
//                    "value" => $product->min_price * 100,
//                ],
//                "buyPrice" => [
//                    "value" => $product->purchase_price * 100,
//                ],
//                'minimumBalance' => $product->min_balance,
//                "images" => $productImages,
//            ]);

//        return json_decode($newProduct->body(), JSON_UNESCAPED_UNICODE);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/product',
            'POST',
            $myWarehouse,
            json_encode([
                "name" => $product->name,
                "code" => (string)$product->id,
                "article" => (string)$product->sku,
                "vat" => $product->vat === -1 ? 0:$product->vat,
                "vatEnabled" => !($product->vat === -1),
                "productFolder" => [
                    "meta" =>  $group['meta'],
                ],
                "description" => $product->description,
                "salePrices" => [
                    [
                        'value' => $product_price,
                        "priceType" => [
                            "meta" => $getPriceTypeValue['meta'],
                        ],
                    ],
                ],
                "uom" => [
                    'meta' => $this->getUom($myWarehouse)['rows'][0]['meta'],
                ],
                "minPrice" => [
                    "value" => $product->min_price * 100,
                ],
                "buyPrice" => [
                    "value" => $product->purchase_price * 100,
                ],
                'minimumBalance' => $product->min_balance,
                "images" => $productImages,
            ], true)
        ));
        $newProduct = curl_exec($curl);
        curl_close($curl);
        return json_decode($newProduct, JSON_UNESCAPED_UNICODE);
    }

    public function createProductVariant($myWarehouse, $product, $variant, $variantImages, $characteristics, $newProduct, $getPriceTypeValue){
//        $newProductVariant = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->withHeaders([
//                "Content-Type" => "application/json"
//            ])
//            ->post('https://online.moysklad.ru/api/remap/1.2/entity/variant', [
//                "name" => $variant['name'],
//                'characteristics' => $characteristics,
//                "code" => (string)$variant['id'],
//                "article" => $variant['sku'],
//                "vat" => $product->vat === -1 ? 0:$product->vat,
//                "vatEnabled" => !($product->vat === -1),
//                "description" => $variant['description'],
//                "salePrices" => [
//                    [
//                        'value' => $variant['price'] * 100,
//                        "priceType" => [
//                            "meta" => $getPriceTypeValue['meta'],
//                        ],
//                    ],
//                ],
//                "minPrice" => [
//                    "value" => $variant['min_price'] * 100,
//                ],
//                "buyPrice" => [
//                    "value" => $variant['purchase_price'] * 100,
//                ],
//                'minimumBalance' => $variant['min_balance'],
//                "product" => [
//                    "meta" => $newProduct['meta'],
//                ],
//                "images" => $variantImages,
//            ]);

//        return json_decode($newProductVariant->body(), JSON_UNESCAPED_UNICODE);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/variant',
            'POST',
            $myWarehouse,
            json_encode([
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
                            "meta" => $getPriceTypeValue['meta'],
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
                    "meta" => $newProduct['meta'],
                ],
                "images" => $variantImages,
            ]), true)
        );
        $newProductVariant = curl_exec($curl);
        curl_close($curl);
        return json_decode($newProductVariant, JSON_UNESCAPED_UNICODE);
    }

    public function getCharacteristics($myWarehouse){
//        $characteristics = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->get("https://online.moysklad.ru/api/remap/1.2/entity/variant/metadata");
//
//        return json_decode($characteristics->body(), JSON_UNESCAPED_UNICODE);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            "https://online.moysklad.ru/api/remap/1.2/entity/variant/metadata",
            'GET',
            $myWarehouse,
        ));
        $characteristics = curl_exec($curl);
        curl_close($curl);
        return json_decode($characteristics, JSON_UNESCAPED_UNICODE);
    }

    public function createCharacteristic($myWarehouse, $optionName){
//        $characteristic = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->withHeaders([
//                "Content-Type" => "application/json"
//            ])
//            ->post("https://online.moysklad.ru/api/remap/1.2/entity/variant/metadata/characteristics", [
//                "name" => $optionName,
//            ]);
//
//        return json_decode($characteristic->body(), JSON_UNESCAPED_UNICODE);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            "https://online.moysklad.ru/api/remap/1.2/entity/variant/metadata/characteristics",
            'POST',
            $myWarehouse,
            json_encode([
                "name" => $optionName,
            ], true)
        ));
        $characteristic = curl_exec($curl);
        curl_close($curl);
        return json_decode($characteristic, JSON_UNESCAPED_UNICODE);
    }

    public function createEnter($myWarehouse, $productOrVariant, $productOrVariantPrice, $newProductOrVariant){
//        $enter = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->withHeaders([
//                "Content-Type" => "application/json"
//            ])
//            ->post('https://online.moysklad.ru/api/remap/1.2/entity/enter', [
//                "name" => "enter".$productOrVariant['id'] ?? "enter".$productOrVariant->id,
//                "organization" => [
//                    "meta" => $this->organization['rows'][0]['meta']
//                ],
//                "store" => [
//                    "meta" => $this->warehouse['rows'][0]['meta'] ??
//                        $this->warehouse['meta']
//                ],
//                "positions" => [
//                    [
//                        "quantity" => (float)$productOrVariant['units_in_stock'] ?? $productOrVariant->units_in_stock,
//                        "price" => $productOrVariantPrice,
//                        "assortment" => [
//                            "meta" => $newProductOrVariant['meta']
//                        ]
//                    ]
//                ],
//            ]);
//
//        return json_decode($enter->body(), JSON_UNESCAPED_UNICODE);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/enter',
            'POST',
            $myWarehouse,
            json_encode([
                "name" => "enter".$productOrVariant['id'] ?? "enter".$productOrVariant->id,
                "organization" => [
                    "meta" => $this->organization['rows'][0]['meta']
                ],
                "store" => [
                    "meta" => $this->warehouse['rows'][0]['meta'] ??
                        $this->warehouse['meta']
                ],
                "positions" => [
                    [
                        "quantity" => (float)$productOrVariant['units_in_stock'] ?? $productOrVariant->units_in_stock,
                        "price" => $productOrVariantPrice,
                        "assortment" => [
                            "meta" => $newProductOrVariant['meta']
                        ]
                    ]
                ],
            ], true)
        ));
        $enter = curl_exec($curl);
        curl_close($curl);
        return json_decode($enter, JSON_UNESCAPED_UNICODE);
    }

    public function createOrder($myWarehouse, $agent, $code, $order, $status, $contract){
        $order = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
            ->withHeaders([
                "Content-Type" => "application/json"
            ])
            ->post('https://online.moysklad.ru/api/remap/1.2/entity/customerorder', [
                "organization" => [
                    "meta" => $this->getOrganization($myWarehouse)['rows'][0]['meta']
                ],
                "agent" => [
                    "meta" => $agent['rows'][0]['meta'] ?? $agent['meta']
                ],
//                "state" => [
//                    "meta" => $status['meta'],
//                ],
                "contract" => [
                    "meta" => $contract['meta'],
                ],
                "store" => [
                    "meta" => $this->getWarehouse($myWarehouse)['rows'][0]['meta'] ??
                        $this->getWarehouse($myWarehouse)['meta']
                ],
                "code" => (string)$code,
                "shipmentAddress" => $order['ship_address'],
                "positions" => $order['positions'],
            ]);

        return json_decode($order->body(), JSON_UNESCAPED_UNICODE);
    }

    public function getAgent($myWarehouse, $agentName){
        $agent = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
            ->get('https://online.moysklad.ru/api/remap/1.2/entity/counterparty/?search='.$agentName);

        return json_decode($agent->body(), JSON_UNESCAPED_UNICODE);
    }

    public function getProductOrVariant($myWarehouse, $type, $productOrVariantId){
        $productOrVariant = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
            ->get('https://online.moysklad.ru/api/remap/1.2/entity/'.$type.'/'.$productOrVariantId);

        return json_decode($productOrVariant->body(), JSON_UNESCAPED_UNICODE);
    }

    public function createAgent($myWarehouse, $agentData){
        $agent = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
            ->withHeaders([
                "Content-Type" => "application/json"
            ])
            ->post('https://online.moysklad.ru/api/remap/1.2/entity/counterparty', $agentData);

        return json_decode($agent->body(), JSON_UNESCAPED_UNICODE);
    }

    public function createDemand($myWarehouse, $agent, $order){
        $demand = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
            ->withHeaders([
                "Content-Type" => "application/json"
            ])
            ->post('https://online.moysklad.ru/api/remap/1.2/entity/demand', [
                "organization" => [
                    "meta" => $this->getOrganization($myWarehouse)['rows'][0]['meta']
                ],
                "agent" => [
                    "meta" => $agent['rows'][0]['meta'] ?? $agent['meta']
                ],
                "store" => [
                    "meta" => $this->getWarehouse($myWarehouse)['rows'][0]['meta'] ??
                        $this->getWarehouse($myWarehouse)['meta']
                ],
                "shipmentAddress" => $order['ship_address'],
                "positions" => $order['positions'],
            ]);

        return json_decode($demand->body(), JSON_UNESCAPED_UNICODE);
    }

    public function createContract($myWarehouse, $agent, $code, $sum){
        $contract = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
            ->withHeaders([
                "Content-Type" => "application/json"
            ])
            ->post('https://online.moysklad.ru/api/remap/1.2/entity/contract', [
                "ownAgent" => [
                    "meta" => $this->getOrganization($myWarehouse)['rows'][0]['meta']
                ],
                "agent" => [
                    "meta" => $agent['rows'][0]['meta'] ?? $agent['meta']
                ],
                'code' => (string)$code,
                "description" => 'Договор купли-продажи на заказ с кодом '.$code,
                "sum" => $sum,
            ]);

        return json_decode($contract->body(), JSON_UNESCAPED_UNICODE);
    }

    public function getUom($myWarehouse){
//        $uom = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/uom/?search=Штука');
//
//        return json_decode($uom->body(), JSON_UNESCAPED_UNICODE);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/uom/?search=%D0%A8%D1%82%D1%83%D0%BA%D0%B0',
            'GET',
            $myWarehouse,
        ));
        $uom = curl_exec($curl);
        curl_close($curl);
        return json_decode($uom, JSON_UNESCAPED_UNICODE);
    }

    public function getEntityStates($myWarehouse, $entity){
        $state = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
            ->get('https://online.moysklad.ru/api/remap/1.2/entity/'.$entity.'/metadata');

        return json_decode($state->body(), JSON_UNESCAPED_UNICODE);
    }
    public function createExportFile($myWarehouse, $order_id){
        $file = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
            ->withHeaders([
                "Content-Type" => "application/json",
            ])
            ->post('https://online.moysklad.ru/api/remap/1.2/entity/customerorder/'.$order_id.'/export/', [
                "template" => [
                    "meta" => [
                        "href" => "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/embeddedtemplate/6ffea5e5-1b69-4a88-be59-4856281d439c",
                        "type" => "embeddedtemplate",
                        "mediaType" => "application/json"
                    ]
                ],
                "extension" => "pdf"
            ]);

        return $file->body();
    }

    public function getEmbeddedTemplate($myWarehouse){
        $template = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
            ->get('https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/embeddedtemplate/');

        return json_decode($template->body(), JSON_UNESCAPED_UNICODE);
    }
}