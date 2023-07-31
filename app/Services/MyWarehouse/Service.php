<?php

namespace App\Services\MyWarehouse;

use App\Models\BannerImage;
use App\Models\Category;
use App\Models\Enter;
use App\Models\Image;
use App\Models\MyWarehouse;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Models\Variant;
use App\Utilities\ImageConvertToBase64;
use App\Utilities\ImageConvertToWebp;
use App\Utilities\TranslationIntoLatin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
                    $variant = is_string($variants->variants_json) ? json_decode($variants->variants_json, true) : $variants->variants_json;
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
                            'variant_id' => $variants_edit[$i]->id,
                            'quantity' => (int)$variant['units_in_stock'],
                        ]);
                    }

                }
            }
            else{
                if ((int)$product->units_in_stock > 0){
                    $enter = $this->createEnter($myWarehouse, $product, $product_price, $newProduct);
                    Enter::firstOrCreate([
                        'enter_id' => $enter['id'],
                        'product_id' => $product->id,
                        'quantity' => (int)$product->units_in_stock,
                    ]);
                }
            }

            $product->update([
                'exported' => true,
            ]);
        }

        return true;
    }

    public function import($entity, $action, $my_warehouse_id, $updated_fields){
        $myWarehouse = MyWarehouse::select('token')->first();
        if ($entity === 'productfolder'){
            $service_category = new \App\Services\Category\Service();

            if ($action === 'CREATE'){
                $category = $this->getCreatedCategory($myWarehouse, $my_warehouse_id);
                $category_name = $category['name'];
                if (isset($category['productFolder'])){
                    $category_parent_id = Category::where('my_warehouse_id', substr($category['productFolder']['meta']['href'], -36, 36))->first()['id'];
                }
                $service_category->store([
                    'name' => $category_name,
                    'my_warehouse_id' => $my_warehouse_id,
                    'parent_id' => $category_parent_id ?? null,
                ]);
            }

            if ($action === 'UPDATE'){
                $created_category = $this->getCreatedCategory($myWarehouse, $my_warehouse_id);
                if ($created_category && count($updated_fields)){
                    $category = Category::where('my_warehouse_id', $my_warehouse_id)->first();
                    if ($category){
                        if (in_array('name', $updated_fields)){
                            $category->update([
                                'name' => $created_category['name'],
                                'slug' => str(TranslationIntoLatin::translate($created_category['name']))->slug() ?? $category->slug,
                            ]);
                        }
                        if (in_array('productFolder', $updated_fields)){
                            if (isset($created_category['productFolder'])){
                                $category_parent_id = Category::where('my_warehouse_id',
                                    substr($created_category['productFolder']['meta']['href'], -36, 36))
                                    ->first()['id'];
                                $category->update([
                                    'parent_id' => $category_parent_id ?? $category->parent_id
                                ]);
                            }
                            else{
                                $category->update([
                                    'parent_id' => null
                                ]);
                            }
                        }
                    }
                }
            }

            if ($action === 'DELETE'){
                $category = Category::where('my_warehouse_id', $my_warehouse_id)->first();
                    if ($category){
                        $category->delete();
                }
            }

        }
        if ($entity === 'product'){
            if ($action === 'UPDATE'){
                if (count($updated_fields)){
                    $product = Product::where('my_warehouse_id', $my_warehouse_id)->first();
                    $product_updated = $this->getProductOrVariant($myWarehouse,'product', $my_warehouse_id);
                    if (in_array('mainImage', $updated_fields) || in_array('image', $updated_fields)){
                        $images = $this->getProductOrVariantImages($myWarehouse, 'product', $my_warehouse_id)['rows'];
                        if (count($images) !== 0){
                            if (in_array('mainImage', $updated_fields)){
                                $url = $images[0]['meta']['downloadHref'];
                                $file_name = $this->getRandomString(40).'.'.explode('.', $images[0]['filename'])[1];
                                $path = substr($_SERVER['DOCUMENT_ROOT'], 0, -6).'storage/app/public/images/products/'.$file_name;
                                $file_path = 'storage/images/products/'.$file_name;
                                file_put_contents($path, $this->downloadImage($myWarehouse, $url));
                                $product_image = 'storage'.str_replace(substr($_SERVER['DOCUMENT_ROOT'], 0, -6).'storage/app/public', '',
                                        ImageConvertToWebp::convert(asset($file_path), true));
                                Storage::disk('public')->delete(substr($product->image, 8));
                                $product->update([
                                    'image' => $product_image ?? $product->image,
                                ]);
                            }
                            if (in_array('image', $updated_fields)) {
                                foreach ($images as $i => $img){
                                    $product_images = $product->images()->orderBy('position')->get();
                                    if ($i >= 1){
                                        $url = $img['meta']['downloadHref'];
                                        $file_name = $this->getRandomString(40).'.'.explode('.', $img['filename'])[1];
                                        $path = substr($_SERVER['DOCUMENT_ROOT'], 0, -6).'storage/app/public/images/thumbimg/'.$file_name;
                                        $file_path = 'storage/images/thumbimg/'.$file_name;
                                        if (count($product_images) !== 0){
                                            if (isset($product_images[$i-1])){
                                                if ($product_images[$i-1]->position === $i
                                                    && $product_images[$i-1]->variant_id === null){
                                                    file_put_contents($path, $this->downloadImage($myWarehouse, $url));
                                                    $image_path = 'storage'.str_replace(substr($_SERVER['DOCUMENT_ROOT'], 0, -6).'storage/app/public', '',
                                                            ImageConvertToWebp::convert(asset($file_path), true));
                                                    Storage::disk('public')->delete(substr($product_images[$i-1]->path, 8));
                                                    $product_images[$i-1]->update([
                                                        'path' => $image_path
                                                    ]);
                                                }
                                            }
                                            else{
                                                file_put_contents($path, $this->downloadImage($myWarehouse, $url));
                                                $image_path = 'storage'.str_replace(substr($_SERVER['DOCUMENT_ROOT'], 0, -6).'storage/app/public', '',
                                                        ImageConvertToWebp::convert(asset($file_path), true));
                                                Image::firstOrCreate([
                                                    'product_id' => $product->id,
                                                    'position' => $i,
                                                    'path' => $image_path,
                                                ]);
                                            }
                                        }
                                        else{
                                            file_put_contents($path, $this->downloadImage($myWarehouse, $url));
                                            $image_path = 'storage'.str_replace(substr($_SERVER['DOCUMENT_ROOT'], 0, -6).'storage/app/public', '',
                                                    ImageConvertToWebp::convert(asset($file_path), true));
                                            Image::firstOrCreate([
                                                'product_id' => $product->id,
                                                'position' => $i,
                                                'path' => $image_path,
                                            ]);
                                        }
                                    }
                                }
                                if (count($images) - 1 < count($product_images)){
                                    for ($j = count($product_images) - 1;$j !== (count($images) - 2);$j--) {
                                        Storage::disk('public')->delete(substr($product_images[$j]->path, 8));
                                        $product_images[$j]->delete();
                                    }
                                }
                            }
                        }
                        else{
                            if ($product->image){
                                Storage::disk('public')->delete(substr($product->image, 8));
                                $product->update([
                                    'image' => null,
                                ]);
                            }
                            if (count($product->images)){
                                foreach ($product->images as $product_image) {
                                    Storage::disk('public')->delete(substr($product_image->path, 8));
                                    $product_image->delete();
                                }
                            }
                        }
                    }
                    if (in_array('name', $updated_fields)){
                        $product->update([
                            'name' => $product_updated['name'] ?? $product->name,
                            'slug' => $product_updated['name'] ?
                                str(TranslationIntoLatin::translate($product_updated['name']))->slug() : $product->slug,
                        ]);
                    }
                    if (in_array('salePrices', $updated_fields)){
                        $product->update([
                            'price' => (int)$product_updated['salePrices'][0]['value'] !== 0 ?
                                (int)($product_updated['salePrices'][0]['value'] / 100) : 0,
                        ]);
                    }
                    if (in_array('description', $updated_fields)){
                        $product->update([
                            'description' => $product_updated['description'] ?? null,
                        ]);
                    }
                    if (in_array('vat', $updated_fields)){
                        $product->update([
                            'vat' => isset($product_updated['vatEnabled']) ?
                                ($product_updated['vatEnabled'] ? $product_updated['vat'] : null) : $product->vat,
                        ]);
                    }
                    if (in_array('minPrice', $updated_fields)){
                        $product->update([
                            'min_price' => (int)$product_updated['minPrice']['value'] !== 0 ?
                                (int)($product_updated['minPrice']['value'] / 100) : 0,
                        ]);
                    }
                    if (in_array('minimumBalance', $updated_fields)){
                        $product->update([
                            'min_balance' => isset($product_updated['minimumBalance']) ? (int)$product_updated['minimumBalance'] : 0,
                        ]);
                    }
                    if (in_array('country', $updated_fields)){
                        $product->update([
                            'country' => isset($product_updated['country']['meta']['href']) ?
                                $this->getCountry($myWarehouse, substr($product_updated['country']['meta']['href'], -36, 36))['name']
                                : null,
                        ]);
                    }
                    if (in_array('buyPrice', $updated_fields)){
                        $product->update([
                            'purchase_price' => (int)$product_updated['buyPrice']['value'] !== 0 ?
                                (int)($product_updated['buyPrice']['value'] / 100) : 0,
                        ]);
                    }
                    if (in_array('article', $updated_fields)){
                        $product->update([
                            'sku' => $product_updated['article'] ?? null,
                        ]);
                    }
                    if (in_array('productFolder', $updated_fields)){
                        $product->update([
                            'category_id' => isset($product_updated['productFolder']['meta']['href']) ?
                                Category::where('my_warehouse_id', substr($product_updated['productFolder']['meta']['href'], -36, 36))->first()['id']
                                : $product->category_id,
                        ]);
                    }
                }
            }
        }
        if ($entity === 'enter' || $entity === 'loss'){
            if ($entity === 'enter'){
                $enter_loss_positions = $this->getCreatedEnterPositions($myWarehouse, $my_warehouse_id);
            }
            else{
                $enter_loss_positions = $this->getCreatedLossPositions($myWarehouse, $my_warehouse_id);
            }
            if ($action === 'CREATE'){
                foreach ($enter_loss_positions['rows'] as $position) {
                    $product_my_warehouse_id = isset($position['assortment']['meta']['href']) ?
                                               substr($position['assortment']['meta']['href'], -36, 36) : null;
                    if ($product_my_warehouse_id){
                        if ($position['assortment']['meta']['type'] === 'product'){
                            $enter_loss_product = Product::where('my_warehouse_id', $product_my_warehouse_id)->first();
                            if ($entity === 'enter'){
                                $enter_loss_product->update([
                                    'units_in_stock' => $enter_loss_product['units_in_stock'] + (int)$position['quantity']
                                ]);
                                Enter::firstOrCreate([
                                    'enter_id' => $my_warehouse_id,
                                    'product_id' => $enter_loss_product['id'],
                                    'quantity' => (int)$position['quantity'],
                                ]);
                            }
                            else{
                                $enter_loss_product->update([
                                    'units_in_stock' => $enter_loss_product['units_in_stock'] - (int)$position['quantity']
                                ]);
                                Enter::firstOrCreate([
                                    'enter_id' => $my_warehouse_id,
                                    'product_id' => $enter_loss_product['id'],
                                    'quantity' => (int)('-'.$position['quantity']),
                                ]);
                            }
                        }
                        if ($position['assortment']['meta']['type'] === 'variant'){
                            $enter_loss_variant = Variant::where('my_warehouse_id', $product_my_warehouse_id)->first();
                            $enter_loss_variants_json = is_string($enter_loss_variant->variants_json)
                                                        ? json_decode($enter_loss_variant->variants_json, true) :
                                                        $enter_loss_variant->variants_json;
                            if ($entity === 'enter'){
                                $enter_loss_variants_json['units_in_stock'] = (int)$enter_loss_variants_json['units_in_stock'] + (int)$position['quantity'];
                                $enter_loss_variant->update([
                                    'variants_json' => json_encode($enter_loss_variants_json, JSON_UNESCAPED_UNICODE)
                                ]);
                                Enter::firstOrCreate([
                                    'enter_id' => $my_warehouse_id,
                                    'product_id' => $enter_loss_variant->product_id,
                                    'variant_id' => $enter_loss_variant->id,
                                    'quantity' => (int)$position['quantity'],
                                ]);
                            }
                            else{
                                $enter_loss_variants_json['units_in_stock'] = (int)$enter_loss_variants_json['units_in_stock'] - (int)$position['quantity'];
                                $enter_loss_variant->update([
                                    'variants_json' => json_encode($enter_loss_variants_json, JSON_UNESCAPED_UNICODE)
                                ]);
                                Enter::firstOrCreate([
                                    'enter_id' => $my_warehouse_id,
                                    'product_id' => $enter_loss_variant['product_id'],
                                    'variant_id' => $enter_loss_variant->id,
                                    'quantity' => (int)('-'.$position['quantity']),
                                ]);
                            }
                        }
                    }
                }
            }

            if ($action === 'UPDATE'){
                $enter_loss = Enter::where('enter_id', $my_warehouse_id)->first();
                if($enter_loss){
                    if (!in_array('applicable', $updated_fields) && in_array('positions', $updated_fields)){
                        foreach ($enter_loss_positions['rows'] as $position) {
                            if ($entity === 'enter'){
                                if ($position['assortment']['meta']['type'] === 'product'){
                                    if ($enter_loss->product_id && $enter_loss->variant_id === null){
//                                    $enter_loss_product = Product::where('id', $enter_loss['product_id'])->first();
                                        $enter_loss->product()->update([
                                            'units_in_stock' => ($enter_loss->product->units_in_stock - $enter_loss['quantity']) + (int)$position['quantity']
                                        ]);
                                    }
                                }
                                if ($position['assortment']['meta']['type'] === 'variant'){
                                    if ($enter_loss->product_id && $enter_loss->variant_id) {
                                        $enter_loss_variant = Variant::where('id', $enter_loss->variant_id)->first();
                                        $enter_loss_variants_json = is_string($enter_loss_variant->variants_json)
                                                                    ? json_decode($enter_loss_variant->variants_json, true) :
                                                                    $enter_loss_variant->variants_json;
                                        $enter_loss_variants_json['units_in_stock'] = ((int)$enter_loss_variants_json['units_in_stock'] - $enter_loss['quantity']) + (int)$position['quantity'];
                                        $enter_loss_variant->update([
                                            'variants_json' => json_encode($enter_loss_variants_json, JSON_UNESCAPED_UNICODE)
                                        ]);
                                    }
                                }
                                $enter_loss->update([
                                    'quantity' => (int)$position['quantity']
                                ]);
                            }
                            else{
                                if ($position['assortment']['meta']['type'] === 'product'){
                                    if ($enter_loss->product_id && $enter_loss->variant_id === null){
//                                    $enter_loss_product = Product::where('id', $enter_loss['product_id'])->first();
                                        $enter_loss->product()->update([
                                            'units_in_stock' => ($enter_loss->product->units_in_stock - $enter_loss['quantity']) - (int)$position['quantity']
                                        ]);
                                    }
                                }
                                if ($position['assortment']['meta']['type'] === 'variant'){
                                    if ($enter_loss->product_id && $enter_loss->variant_id) {
                                        $enter_loss_variant = Variant::where('id', $enter_loss->variant_id)->first();
                                        $enter_loss_variants_json = is_string($enter_loss_variant->variants_json)
                                                                    ? json_decode($enter_loss_variant->variants_json, true) :
                                                                    $enter_loss_variant->variants_json;
                                        $enter_loss_variants_json['units_in_stock'] = ((int)$enter_loss_variants_json['units_in_stock'] - $enter_loss['quantity']) - (int)$position['quantity'];
                                        $enter_loss_variant->update([
                                            'variants_json' => json_encode($enter_loss_variants_json, JSON_UNESCAPED_UNICODE)
                                        ]);
                                    }
                                }
                                $enter_loss->update([
                                    'quantity' => (int)('-'.$position['quantity'])
                                ]);
                            }
                        }
                    }
                    if (in_array('applicable', $updated_fields)){
                        foreach ($enter_loss_positions['rows'] as $position) {
//                        $enter_loss_product = Product::where('id', $enter_loss['product_id'])->first();
                            if ($position['assortment']['meta']['type'] === 'product') {
                                if ($enter_loss->product_id && $enter_loss->variant_id === null){
                                    $enter_loss->product()->update([
                                        'units_in_stock' => $enter_loss->product->units_in_stock - $enter_loss['quantity']
                                    ]);
                                }
                            }
                            if ($position['assortment']['meta']['type'] === 'variant'){
                                if ($enter_loss->product_id && $enter_loss->variant_id) {
                                    $enter_loss_variant = Variant::where('id', $enter_loss->variant_id)->first();
                                    $enter_loss_variants_json = is_string($enter_loss_variant->variants_json)
                                                                ? json_decode($enter_loss_variant->variants_json, true) :
                                                                $enter_loss_variant->variants_json;
                                    $enter_loss_variants_json['units_in_stock'] = (int)$enter_loss_variants_json['units_in_stock'] - $enter_loss['quantity'];
                                    $enter_loss_variant->update([
                                        'variants_json' => json_encode($enter_loss_variants_json, JSON_UNESCAPED_UNICODE)
                                    ]);
                                }
                            }
                            $enter_loss->delete();
                        }
                    }
                }
            }

        }

        if($entity === 'customerorder'){
            if ($action === 'UPDATE'){
                $created_order = $this->getCreatedOrder($myWarehouse, $my_warehouse_id);
                $order = Order::with('status')->where('my_warehouse_id', $my_warehouse_id)->first();
                if ($created_order && $order){
                    if (!in_array('applicable', $updated_fields)){
                        $status_name = $this->getOrderState($myWarehouse, substr($created_order['state']['meta']['href'], -36, 36))['name'];
                        $status = OrderStatus::where('name', $status_name)->first();
                        if (!$status){
                            OrderStatus::firstOrCreate([
                                'name' =>  $status_name
                            ]);
                            $status = OrderStatus::where('name', $status_name)->first();
                        }
                        $order->update([
                            'ship_address' => $created_order['shipmentAddress'] ?? $order->ship_address,
                            'description' => $created_order['description'] ?? $order->description,
                            'status_id' => $status['id']
                        ]);
                    }
                    if (in_array('applicable', $updated_fields)){
                        $order->delete();
                    }
                }
            }
        }

        if ($entity === 'variant'){
            if ($action === 'UPDATE'){
                if (count($updated_fields)){
                    $flag = false;
                    $created_variant = $this->getProductOrVariant($myWarehouse, 'variant', $my_warehouse_id);
                    $product = Product::where('my_warehouse_id', substr($created_variant['product']['meta']['href'], -36, 36))->first();
                    $variant = Variant::where('my_warehouse_id', $my_warehouse_id)->first();
//                    if (count($product->variants) !== 0){
//                        foreach ($product->variants as $p_variant) {
//                            if ($p_variant->variants_json['my_warehouse_id'] === $my_warehouse_id){
//                                $variant =  $p_variant;
//                            }
//                        }
//                    }
                    if ($variant){
                        $variants_json = is_string($variant->variants_json)
                                        ? json_decode($variant->variants_json, true) :
                                        $variant->variants_json;
                        if (in_array('attributes', $updated_fields)){
                            $flag = true;
                            $variants_json_name = explode(")", explode('(', $created_variant['name'])[1])[0] ?? $variants_json['name'];
                            $variants_json_product_name = trim(str_replace($variants_json['name'], '', $variants_json['product_name'])).' '.$variants_json_name;
                            $variants_json['name'] = $variants_json_name;
                            $variants_json['product_name'] = $variants_json_product_name;
                            $variants_json['slug'] = str(TranslationIntoLatin::translate($variants_json_product_name))->slug();
                        }
                        if (in_array('description', $updated_fields)){
                            $flag = true;
                            $variants_json['description'] = $created_variant['description'] ?? "";
                        }
//                        if (in_array('minimumBalance', $updated_fields)){
//                            $flag = true;
//                            $variants_json['min_balance'] = isset($created_variant['minimumBalance']) ? (int)$created_variant['minimumBalance'] : 0;
//                        }
                        if (in_array('salePrices', $updated_fields)){
                            $flag = true;
                            $variants_json['price'] = (int)($created_variant['salePrices'][0]['value'] / 100);
                        }
                        if (in_array('minPrice', $updated_fields)){
                            $flag = true;
                            $variants_json['min_price'] = isset($created_variant['minPrice']) ?
                                                          (int)($created_variant['minPrice']['value'] / 100) : $product->min_price;
                        }
                        if (in_array('buyPrice', $updated_fields)){
                            $flag = true;
                            $variants_json['purchase_price'] = isset($created_variant['buyPrice']) ?
                                (int)($created_variant['buyPrice']['value'] / 100) : $product->purchase_price;
                        }

                        if (in_array('mainImage', $updated_fields) || in_array('images', $updated_fields)){
                            $images = $this->getProductOrVariantImages($myWarehouse, 'variant', $my_warehouse_id)['rows'];
                            if (count($images) !== 0){
                                if (in_array('mainImage', $updated_fields)){
                                    $url = $images[0]['meta']['downloadHref'];
                                    $file_name = $this->getRandomString(40).'.'.explode('.', $images[0]['filename'])[1];
                                    $path = substr($_SERVER['DOCUMENT_ROOT'], 0, -6).'storage/app/public/images/products/'.$file_name;
                                    $file_path = 'storage/images/products/'.$file_name;
                                    file_put_contents($path, $this->downloadImage($myWarehouse, $url));
                                    $variant_image = 'storage'.str_replace(substr($_SERVER['DOCUMENT_ROOT'], 0, -6).'storage/app/public', '',
                                            ImageConvertToWebp::convert(asset($file_path), true));
                                    if ($variants_json['image'] !== $product->image){
                                        Storage::disk('public')->delete(substr($variants_json['image'], 8));
                                    }
                                    $flag = true;
                                    $variants_json['image'] = $variant_image;
                                }
                                if (in_array('images', $updated_fields)) {
                                    foreach ($images as $i => $img){
                                        $variant_images = Image::where('product_id', $variant->product_id)
                                            ->where('variant_id', (int)$variants_json['id'])
                                            ->orderBy('position')->get();
                                        if ($i >= 1){
                                            $url = $img['meta']['downloadHref'];
                                            $file_name = $this->getRandomString(40).'.'.explode('.', $img['filename'])[1];
                                            $path = substr($_SERVER['DOCUMENT_ROOT'], 0, -6).'storage/app/public/images/thumbimg/'.$file_name;
                                            $file_path = 'storage/images/thumbimg/'.$file_name;
                                            if (count($variant_images) !== 0){
                                                if (isset($variant_images[$i-1])){
                                                    if ($variant_images[$i-1]->position === $i
                                                        && $variant_images[$i-1]->variant_id === null){
                                                        file_put_contents($path, $this->downloadImage($myWarehouse, $url));
                                                        $image_path = 'storage'.str_replace(substr($_SERVER['DOCUMENT_ROOT'], 0, -6).'storage/app/public', '',
                                                                ImageConvertToWebp::convert(asset($file_path), true));
                                                        Storage::disk('public')->delete(substr($variant_images[$i-1]->path, 8));
                                                        $variant_images[$i-1]->update([
                                                            'path' => $image_path
                                                        ]);
                                                    }
                                                }
                                                else{
                                                    file_put_contents($path, $this->downloadImage($myWarehouse, $url));
                                                    $image_path = 'storage'.str_replace(substr($_SERVER['DOCUMENT_ROOT'], 0, -6).'storage/app/public', '',
                                                            ImageConvertToWebp::convert(asset($file_path), true));
                                                    Image::firstOrCreate([
                                                        'product_id' => $product->id,
                                                        'variant_id' => (int)$variants_json['id'],
                                                        'position' => $i,
                                                        'path' => $image_path,
                                                    ]);
                                                }
                                            }
                                            else{
                                                file_put_contents($path, $this->downloadImage($myWarehouse, $url));
                                                $image_path = 'storage'.str_replace(substr($_SERVER['DOCUMENT_ROOT'], 0, -6).'storage/app/public', '',
                                                        ImageConvertToWebp::convert(asset($file_path), true));
                                                Image::firstOrCreate([
                                                    'product_id' => $product->id,
                                                    'variant_id' => (int)$variants_json['id'],
                                                    'position' => $i,
                                                    'path' => $image_path,
                                                ]);
                                            }
                                        }
                                    }
                                    if (count($images) - 1 < count($variant_images)){
                                        for ($j = count($variant_images) - 1;$j !== (count($images) - 2);$j--) {
                                            Storage::disk('public')->delete(substr($variant_images[$j]->path, 8));
                                            $variant_images[$j]->delete();
                                        }
                                    }
                                }
                            }
                            else{
                                if ($variants_json['image']){
                                    if ($variants_json['image'] !== $product->image){
                                        Storage::disk('public')->delete(substr($variants_json['image'], 8));
                                    }
                                    $variants_json['image'] = '';
                                    $flag = true;
                                }
                                $variant_images = Image::where('product_id', $variant->product_id)
                                    ->where('variant_id', (int)$variants_json['id'])->get();
                                if (count($variant_images)){
                                    foreach ($variant_images as $variant_image) {
                                        Storage::disk('public')->delete(substr($variant_image->path, 8));
                                        $variant_image->delete();
                                    }
                                }
                            }
                        }

                        if ($flag){
                            Variant::where('id', $variant->id)->update([
                                'variants_json' => json_encode($variants_json, JSON_UNESCAPED_UNICODE),
                            ]);
                        }
                    }
                }
            }
            if ($action === 'DELETE'){
                $variant = Variant::where('my_warehouse_id', $my_warehouse_id)->first();
                if ($variant){
                    $variant->delete();
                }
            }
        }

    }

    public function getRandomString($n){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
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
//        $order = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->withHeaders([
//                "Content-Type" => "application/json"
//            ])
//            ->post('https://online.moysklad.ru/api/remap/1.2/entity/customerorder', [
//                "organization" => [
//                    "meta" => $this->getOrganization($myWarehouse)['rows'][0]['meta']
//                ],
//                "agent" => [
//                    "meta" => $agent['rows'][0]['meta'] ?? $agent['meta']
//                ],
//                "contract" => [
//                    "meta" => $contract['meta'],
//                ],
//                "store" => [
//                    "meta" => $this->getWarehouse($myWarehouse)['rows'][0]['meta'] ??
//                        $this->getWarehouse($myWarehouse)['meta']
//                ],
//                "code" => (string)$code,
//                "shipmentAddress" => $order['ship_address'],
//                "positions" => $order['positions'],
//            ]);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/customerorder',
            'POST',
            $myWarehouse,
            json_encode([
                "organization" => [
                    "meta" => $this->getOrganization($myWarehouse)['rows'][0]['meta']
                ],
                "agent" => [
                    "meta" => $agent['rows'][0]['meta'] ?? $agent['meta']
                ],
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
            ], true)
        ));
        $order = curl_exec($curl);
        curl_close($curl);

        return json_decode($order, JSON_UNESCAPED_UNICODE);
    }

    public function getAgent($myWarehouse, $agentName){
//        $agent = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/counterparty/?search='.$agentName);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/counterparty/?search='.$agentName,
            'GET',
            $myWarehouse,
        ));
        $agent = curl_exec($curl);
        curl_close($curl);

        return json_decode($agent, JSON_UNESCAPED_UNICODE);
    }

    public function getProductOrVariant($myWarehouse, $type, $productOrVariantId){
//        $productOrVariant = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/'.$type.'/'.$productOrVariantId);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/'.$type.'/'.$productOrVariantId,
            'GET',
            $myWarehouse,
        ));
        $productOrVariant = curl_exec($curl);
        curl_close($curl);

        return json_decode($productOrVariant, JSON_UNESCAPED_UNICODE);
    }

    public function createAgent($myWarehouse, $agentData){
//        $agent = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->withHeaders([
//                "Content-Type" => "application/json"
//            ])
//            ->post('https://online.moysklad.ru/api/remap/1.2/entity/counterparty', $agentData);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/counterparty',
            'POST',
            $myWarehouse,
            $agentData
        ));
        $agent = curl_exec($curl);
        curl_close($curl);

        return json_decode($agent, JSON_UNESCAPED_UNICODE);
    }

    public function createDemand($myWarehouse, $agent, $order){
//        $demand = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->withHeaders([
//                "Content-Type" => "application/json"
//            ])
//            ->post('https://online.moysklad.ru/api/remap/1.2/entity/demand', [
//                "organization" => [
//                    "meta" => $this->getOrganization($myWarehouse)['rows'][0]['meta']
//                ],
//                "agent" => [
//                    "meta" => $agent['rows'][0]['meta'] ?? $agent['meta']
//                ],
//                "store" => [
//                    "meta" => $this->getWarehouse($myWarehouse)['rows'][0]['meta'] ??
//                        $this->getWarehouse($myWarehouse)['meta']
//                ],
//                "shipmentAddress" => $order['ship_address'],
//                "positions" => $order['positions'],
//            ]);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/demand',
            'POST',
            $myWarehouse,
            json_encode([
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
            ], true)
        ));
        $demand = curl_exec($curl);
        curl_close($curl);

        return json_decode($demand, JSON_UNESCAPED_UNICODE);
    }

    public function createContract($myWarehouse, $agent, $code, $sum){
//        $contract = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->withHeaders([
//                "Content-Type" => "application/json"
//            ])
//            ->post('https://online.moysklad.ru/api/remap/1.2/entity/contract', [
//                "ownAgent" => [
//                    "meta" => $this->getOrganization($myWarehouse)['rows'][0]['meta']
//                ],
//                "agent" => [
//                    "meta" => $agent['rows'][0]['meta'] ?? $agent['meta']
//                ],
//                'code' => (string)$code,
//                "description" => 'Договор купли-продажи на заказ с кодом '.$code,
//                "sum" => $sum,
//            ]);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/contract',
            'POST',
            $myWarehouse,
            json_encode([
                "ownAgent" => [
                    "meta" => $this->getOrganization($myWarehouse)['rows'][0]['meta']
                ],
                "agent" => [
                    "meta" => $agent['rows'][0]['meta'] ?? $agent['meta']
                ],
                'code' => (string)$code,
                "description" => 'Договор купли-продажи на заказ с кодом '.$code,
                "sum" => $sum,
            ], true)
        ));
        $contract = curl_exec($curl);
        curl_close($curl);

        return json_decode($contract, JSON_UNESCAPED_UNICODE);
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
//        $state = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/'.$entity.'/metadata');

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/'.$entity.'/metadata',
            'GET',
            $myWarehouse,
        ));
        $state = curl_exec($curl);
        curl_close($curl);
        dd(json_decode($state, JSON_UNESCAPED_UNICODE));
        return json_decode($state, JSON_UNESCAPED_UNICODE);
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

//        $curl = curl_init();
//        curl_setopt_array($curl, $this->getCurlOptArray(
//            'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/'.$order_id.'/export/',
//            'POST',
//            $myWarehouse,
//            json_encode([
//                "template" => [
//                    "meta" => [
//                        "href" => "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/embeddedtemplate/6ffea5e5-1b69-4a88-be59-4856281d439c",
//                        "type" => "embeddedtemplate",
//                        "mediaType" => "application/json"
//                    ]
//                ],
//                "extension" => "pdf"
//            ], true)
//        ));
//        $file = curl_exec($curl);
//        curl_close($curl);
//
//        return $file;
    }

    public function getEmbeddedTemplate($myWarehouse){
//        $template = Http::withOptions(['connect_timeout' => 180, 'read_timeout'  =>  180])->withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/embeddedtemplate/');

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/embeddedtemplate/',
            'GET',
            $myWarehouse,
        ));
        $template = curl_exec($curl);
        curl_close($curl);

        return json_decode($template, JSON_UNESCAPED_UNICODE);
    }

    public function getCreatedCategory($myWarehouse, $my_warehouse_category_id){
//        $category = Http::withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/productfolder/'.$my_warehouse_category_id);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/productfolder/'.$my_warehouse_category_id,
            'GET',
            $myWarehouse,
        ));
        $category = curl_exec($curl);
        curl_close($curl);

        return json_decode($category, JSON_UNESCAPED_UNICODE);
    }

    public function getCountry($myWarehouse, $my_warehouse_country_id){
//        $country = Http::withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/country/'.$my_warehouse_country_id);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/country/'.$my_warehouse_country_id,
            'GET',
            $myWarehouse,
        ));
        $country = curl_exec($curl);
        curl_close($curl);

        return json_decode($country, JSON_UNESCAPED_UNICODE);
    }

    public function getCreatedEnterPositions($myWarehouse, $my_warehouse_enter_id){
//        $enter_positions = Http::withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/enter/'.$my_warehouse_enter_id.'/positions');

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/enter/'.$my_warehouse_enter_id.'/positions',
            'GET',
            $myWarehouse,
        ));
        $enter_positions = curl_exec($curl);
        curl_close($curl);

        return json_decode($enter_positions, JSON_UNESCAPED_UNICODE);
    }

    public function getCreatedLossPositions($myWarehouse, $my_warehouse_loss_id){
//        $loss_positions = Http::withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/loss/'.$my_warehouse_loss_id.'/positions');

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/loss/'.$my_warehouse_loss_id.'/positions',
            'GET',
            $myWarehouse,
        ));
        $loss_positions = curl_exec($curl);
        curl_close($curl);

        return json_decode($loss_positions, JSON_UNESCAPED_UNICODE);
    }

    public function getCreatedOrder($myWarehouse, $my_warehouse_order_id){
//        $order = Http::withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/customerorder/'.$my_warehouse_order_id);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/'.$my_warehouse_order_id,
            'GET',
            $myWarehouse,
        ));
        $order = curl_exec($curl);
        curl_close($curl);

        return json_decode($order, JSON_UNESCAPED_UNICODE);
    }

    public function getOrderState($myWarehouse, $my_warehouse_state_id){
//        $state = Http::withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/'.$my_warehouse_state_id);

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/states/'.$my_warehouse_state_id,
            'GET',
            $myWarehouse,
        ));
        $state = curl_exec($curl);
        curl_close($curl);

        return json_decode($state, JSON_UNESCAPED_UNICODE);
    }

    public function getProductOrVariantImages($myWarehouse, $type, $my_warehouse_product_id){
//        $image = Http::withToken($myWarehouse->token)
//            ->get('https://online.moysklad.ru/api/remap/1.2/entity/'.$type.'/'.$my_warehouse_product_id.'/images');

        $curl = curl_init();
        curl_setopt_array($curl, $this->getCurlOptArray(
            'https://online.moysklad.ru/api/remap/1.2/entity/'.$type.'/'.$my_warehouse_product_id.'/images',
            'GET',
            $myWarehouse,
        ));
        $image = curl_exec($curl);
        curl_close($curl);

        return json_decode($image, JSON_UNESCAPED_UNICODE);
    }

    public function downloadImage($myWarehouse, $url){
        $image = Http::withToken($myWarehouse->token)->withHeaders([
            "Content-Type" => "application/json",
        ])->get($url);

//        $curl = curl_init();
//        curl_setopt_array($curl, $this->getCurlOptArray(
//            $url,
//            'GET',
//            $myWarehouse,
//        ));
//        $image = curl_exec($curl);
//        curl_close($curl);
//
//        return $image;
    }
}