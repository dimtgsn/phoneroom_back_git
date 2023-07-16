<?php

namespace App\Services\Order;

use App\Models\MyWarehouse;
use App\Models\Order;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;

class Service
{
    public function create($data)
    {
        $order = false;
        $order = \DB::transaction(function() use ($data) {

            $order = Order::firstOrCreate([
                'user_id' => $data['user_id'],
                'status_id' => 5,
                'total' => (int)$data['total'],
                'ship_address' => json_decode($data['ship_address'], true)['address'],
                'zip' => json_decode($data['ship_address'], true)['zip'],
            ]);
            foreach (json_decode($data['details'], true) as $product){
                $pr = Product::where('id', $product['id'])->first();
                if ($pr){
                    $price = $pr->price;
                } else{
                    $product_id = $product['id'];
                    $variant = DB::select("
                        select variants_json->>0 as data , product_id
                        from variants
                        where (variants_json->>0)::jsonb  @> '{\"id\": \"$product_id\"}'"
                    )[0]->data;
                    $price = (int)json_decode($variant, true)['price'];
                }
                \DB::table('order_products')->insert([
                    'quantity' => $product['quantity'],
                    'product_id' => $product['id'],
                    'price' => $price,
                    'order_id' => $order->id,
                ]);
                $pr->update([
                    'units_in_stock' => (int)$pr->units_in_stock - (int)$product['quantity']
                ]);
            }

            return $order;
        });

        if ($order){
            // TODO new code
            $new_order = $this->export_order($order, new \App\Services\MyWarehouse\Service);
             if($new_order !== false){
                 return [
                        'myWarehouseNewOrderId' => $new_order['id'],
                        'order' => $order
                     ];
             }
             $order->delete();
             return false;
        }

        return false;
    }

    public function export_order($order, $service)
    {
        $myWarehouse = MyWarehouse::select('token')->first();
        $data = $this->get_order_products($order, false);
        $products = $data['products'];
        $client_data = [
            'name' => $order->user->profile->last_name.' '.$order->user->first_name.' '.$order->user->profile->middle_name,
            'email' => $order->user->email ?? '',
            'phone' => $order->user->phone,
            'companyType' => 'individual',
            'actualAddress' => $order->zip.', '.$order->ship_address
        ];
        $order_data = [
            'ship_address' => $order->zip.', '.$order->ship_address,
            'positions' => [],
        ];
        $sum = 0;
        $status = '';
        foreach ($service->getEntityStates($myWarehouse, 'customerorder')['states'] as $state) {
            if($state['name'] === $order->status->name){
                $status = $state;
            }
        };
        foreach($products as $product){
            $order_data['positions'][] = [
                "quantity" => $product["quantity"],
                "price" => (int)$product["price"] * 100,
                "vat" => $product['vat'] === -1 ? 0 : $product['vat'],
                "vatEnabled" => !($product['vat'] === -1),
                "assortment" => [
                    'meta' => $service->getProductOrVariant($myWarehouse, $product['type'], $product['product']['my_warehouse_id'])['meta']
                ],
            ];
            $sum += (int)$product["price"] * 100;
        }
        $agent = $service->getAgent($myWarehouse, $client_data['name']);
        if (count($agent['rows']) === 0){
            $agent = $service->createAgent($myWarehouse, $client_data);
        }
        $contract = $service->createContract($myWarehouse, $agent, $order->id, $sum);
        $newOrder = $service->createOrder($myWarehouse, $agent, $order->id, $order_data, $status, $contract);
        $service->createDemand($myWarehouse, $agent, $order_data);

        return $newOrder ?? false;
    }

    public function get_order_products(Order $order, $need_weight=true)
    {
        $products = [];
        $products_weight = 0;
        $order_product = \DB::table("order_products")->where('order_id', $order->id)->select('product_id', 'order_id', 'quantity', 'price', 'created_at')->get();
        foreach ($order_product as $op) {
            $product = Product::where('id', $op->product_id)->first();
            if ($product){
                if ($need_weight){
                    $products_weight += (int)preg_replace("/[^0-9]/", '', json_decode($product->property->properties_json, true)["Вес"]["Вес"]
                        ?? json_decode($product->property->properties_json, true)["Вес"]);
                }
                $products[] = [
                    'product' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'image' => $product->image,
                        'my_warehouse_id' => $product->my_warehouse_id ?? null,
                    ],
                    'type' => 'product',
                    'quantity' => $op->quantity,
                    'price' => $op->price,
                    'vat' => $product->vat,
                ];
                if ($need_weight){
                    $products[count($products)-1] += [
                        'weight' => (int)preg_replace("/[^0-9]/", '', json_decode($product->property->properties_json, true)["Вес"]["Вес"]
                            ?? json_decode($product->property->properties_json, true)["Вес"]),
                    ];
                }
            }
            else{
                foreach (Variant::all() as $variants){
                    if ((int)json_decode($variants->variants_json, true)['id'] === $op->product_id){
                        $variant = json_decode($variants->variants_json, true);
                        if ($need_weight){
                            $products_weight += (int)preg_replace("/[^0-9]/", '', json_decode(Product::where('id', $variants->product_id)->first()->property->properties_json, true)["Вес"]["Вес"]
                                ?? json_decode(Product::where('id', $variants->product_id)->first()->property->properties_json, true)["Вес"]);
                        }
                        $products[] = [
                            'product' => [
                                'id' => $variant['id'],
                                'name' => $variant['product_name'],
                                'image' => $variant['image'],
                                'my_warehouse_id' => $variant['my_warehouse_id'] ?? null,
                            ],
                            'type' => 'variant',
                            'quantity' => $op->quantity,
                            'price' => $op->price,
                            'vat' => Product::where('id', $variants->product_id)->first()->vat,
                        ];
                        if ($need_weight){
                            $products[count($products)-1] += [
                                'weight' => (int)preg_replace("/[^0-9]/", '', json_decode(Product::where('id', $variants->product_id)->first()->property->properties_json, true)["Вес"]["Вес"]
                                    ?? json_decode(Product::where('id', $variants->product_id)->first()->property->properties_json, true)["Вес"]),
                            ];
                        }
                    }
                }
            }
        }

        return [
            'products' => $products,
            'products_weight' => $products_weight,
        ];
    }

}