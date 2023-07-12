<?php

use App\Http\Resources\BannerImage\BannerImageCollection;
use App\Http\Resources\MainImage\MainImageCollection;
use App\Http\Resources\PromotionImage\PromotionImageCollection;
use App\Models\BannerImage;
use App\Models\MainImage;
use App\Models\PromotionImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// TODO delete login, register
Route::group(['namespace' => '\App\Http\Controllers\Api\User', 'prefix' => 'users'], function (){
    Route::post('/phone_auth', 'UserController@phone_auth')->name('api.users.phone_auth');
    Route::get('/userIpInfo', 'UserController@getIpInfo')->name('api.users.getIpInfo');
});

Route::group(['middleware'=>['auth:sanctum', 'json']], function (){

    Route::group(['namespace' => '\App\Http\Controllers\Api\User', 'prefix' => 'users'], function (){
        Route::get('/user/{user}', 'UserController@index')->name('api.users.index');
        Route::patch('/{user}', 'UserController@update')->name('api.users.update');
    });
    Route::group(['namespace' => '\App\Http\Controllers\Api\Message', 'prefix' => 'messages'], function (){
        Route::post('/{user}/create', 'MessageController@create')->name('api.messages.create');
    });
    Route::group(['namespace' => '\App\Http\Controllers\Api\Basket', 'prefix' => 'baskets'], function (){
        Route::patch('/{user}', 'BasketController@update')->name('api.baskets.update');
        Route::get('/{user}', 'BasketController@index')->name('api.baskets.index');
//        Route::get('/count/{user}', 'BasketController@count')->name('api.baskets.count');
        Route::delete('/{user}', 'BasketController@destroy')->name('api.baskets.destroy');
    });
    Route::group(['namespace' => '\App\Http\Controllers\Api\Favorite', 'prefix' => 'favorites'], function (){
        Route::patch('/{user}', 'FavoriteController@update')->name('api.favorites.update');
        Route::get('/{user}', 'FavoriteController@index')->name('api.favorites.index');
        Route::delete('/{user}', 'FavoriteController@destroy')->name('api.favorites.destroy');
    });
    Route::group(['namespace' => '\App\Http\Controllers\Api\Compare', 'prefix' => 'compares'], function (){
        Route::patch('/{user}', 'CompareController@update')->name('api.compares.update');
        Route::get('/{user}', 'CompareController@index')->name('api.compares.index');
        Route::get('/category/{user}', 'CompareController@category')->name('api.compares.category');
        Route::delete('/{user}', 'CompareController@destroy')->name('api.compares.destroy');
    });

    Route::group(['namespace' => '\App\Http\Controllers\Api\Order', 'prefix' => 'orders'], function (){
        Route::post('/create', 'OrderController@create')->name('api.orders.create');
    });
});

Route::group(['namespace' => '\App\Http\Controllers\Api\Product', 'prefix' => 'products'], function (){
    Route::get('/', 'ProductController@index')->name('api.products.index');
    Route::get('/products', 'ProductController@products')->name('api.products.products');
    Route::get('/{slug}', 'ProductController@show')->name('api.products.show');
});

Route::group(['namespace' => '\App\Http\Controllers\Api\Tag', 'prefix' => 'tags'], function (){
    Route::get('/{tag:slug}', 'TagController@index')->name('api.tags.index');
});

Route::group(['namespace' => '\App\Http\Controllers\Api\Brand', 'prefix' => 'brands'], function (){
    Route::get('/', 'BrandController@index')->name('api.brands.index');
});

Route::group(['namespace' => '\App\Http\Controllers\Api\Category', 'prefix' => 'categories'], function (){
    Route::get('/', 'CategoryController@index')->name('api.categories.index');
    Route::get('/{category:slug}', 'CategoryController@show')->name('api.categories.show');
    Route::get('/price/{category:name}', 'CategoryController@prices')->name('api.categories.prices');
    Route::get('/category/{id}', 'CategoryController@subCategories')->name('api.categories.subCategories');
});
// TODO если что удалить этот кеш и поменять на redis
Route::get('main_images', function (){

//    return new MainImageCollection(MainImage::select('path')->orderBy('position', 'ASC')->get());
    return new MainImageCollection(
        Cache::remember('main_image', 60*60*24, function (){
            return MainImage::select('path')->orderBy('position', 'ASC')->get();
        })
    );
})->name('api.main_images.index');

Route::get('banner_images', function (){

//    return new BannerImageCollection(BannerImage::select('path')->orderBy('position', 'ASC')->get());
    return new BannerImageCollection(
        Cache::remember('banner_image', 60*60*24, function (){
            return BannerImage::select('path')->orderBy('position', 'ASC')->get();
        })
    );
})->name('api.banner_images.index');

Route::get('promotion_images', function (){

//    return new PromotionImageCollection(PromotionImage::select('path')->orderBy('position', 'ASC')->get());
    return new PromotionImageCollection(
        Cache::remember('promotion_image', 60*60*24, function (){
            return PromotionImage::select('path')->orderBy('position', 'ASC')->get();
        })
    );
})->name('api.promotion_images.index');

// TODO перекинуть в только для авторизированных пользователей
Route::group(['namespace' => '\App\Http\Controllers\Api\Order', 'prefix' => 'orders'], function (){
    Route::get('/{user}', 'OrderController@index')->name('api.orders.index');
//    Route::post('/create', 'OrderController@create')->name('api.orders.create');
    Route::get('/zip_check', 'OrderController@zip_check')->name('api.orders.zip_check');
});

Route::group(['namespace' => '\App\Http\Controllers\Api\Comment', 'prefix' => 'comments'], function (){
    Route::get('/', 'CommentController@index')->name('api.comments.index');
    Route::post('/create', 'CommentController@create')->name('api.comments.create');
    Route::patch('/{comment}', 'CommentController@update')->name('api.comments.update');
});
