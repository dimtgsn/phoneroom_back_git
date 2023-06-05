<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();

});

Route::group(['namespace' => '\App\Http\Controllers\Api\User', 'prefix' => 'users'], function (){
    Route::post('/phone_auth', 'UserController@phone_auth')->name('api.users.phone_auth');
    Route::post('/register', 'UserController@register')->name('api.users.register');
    Route::post('/login/{user:phone}', 'UserController@login')->name('api.users.login');
    Route::get('/userIpInfo', 'UserController@getIpInfo')->name('api.users.getIpInfo');

});
Route::group(['middleware'=>['auth:sanctum', 'json',]], function (){

    Route::group(['namespace' => '\App\Http\Controllers\Api\User', 'prefix' => 'users'], function (){
        Route::get('/user/{user}', 'UserController@index')->name('api.users.index');
        Route::post('/{user}', 'UserController@update')->name('api.users.update');
        Route::get('/logout/{user}', 'UserController@logout')->name('api.users.logout');
    });
    Route::group(['namespace' => '\App\Http\Controllers\Api\Message', 'prefix' => 'messages'], function (){
        Route::post('/{user}/create', 'MessageController@create')->name('api.messages.create');
    });
    Route::group(['namespace' => '\App\Http\Controllers\Api\Basket', 'prefix' => 'baskets'], function (){
        Route::post('/{user}', 'BasketController@update')->name('api.baskets.update');
        Route::get('/{user}', 'BasketController@index')->name('api.baskets.index');
//        Route::get('/count/{user}', 'BasketController@count')->name('api.baskets.count');
        Route::delete('/{user}', 'BasketController@destroy')->name('api.baskets.destroy');
    });
    Route::group(['namespace' => '\App\Http\Controllers\Api\Favorite', 'prefix' => 'favorites'], function (){
        Route::post('/{user}', 'FavoriteController@update')->name('api.favorites.update');
        Route::get('/{user}', 'FavoriteController@index')->name('api.favorites.index');
        Route::delete('/{user}', 'FavoriteController@destroy')->name('api.favorites.destroy');
    });
    Route::group(['namespace' => '\App\Http\Controllers\Api\Compare', 'prefix' => 'compares'], function (){
        Route::post('/{user}', 'CompareController@update')->name('api.compares.update');
        Route::get('/{user}', 'CompareController@index')->name('api.compares.index');
        Route::get('/category/{user}', 'CompareController@category')->name('api.compares.category');
        Route::delete('/{user}', 'CompareController@destroy')->name('api.compares.destroy');
    });

//    Route::group(['namespace' => '\App\Http\Controllers\Api\Order', 'prefix' => 'orders'], function (){
////    Route::get('/', 'CategoryController@index')->name('api.categories.index');
//        Route::post('/create', 'OrderController@create')->name('api.orders.create');
////    Route::post('/', 'UserController@index')->name('api.users.index');
//    });
});

Route::group(['namespace' => '\App\Http\Controllers\Api\Product', 'prefix' => 'products'], function (){
    Route::get('/', 'ProductController@index')->name('api.products.index');
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

Route::group(['namespace' => '\App\Http\Controllers\Api\MainImage', 'prefix' => 'main_images'], function (){
    Route::get('/', 'MainImageController@index')->name('api.main_images.index');
});
Route::group(['namespace' => '\App\Http\Controllers\Api\BannerImage', 'prefix' => 'banner_images'], function (){
    Route::get('/', 'BannerImageController@index')->name('api.banner_images.index');
});
Route::group(['namespace' => '\App\Http\Controllers\Api\PromotionImage', 'prefix' => 'promotion_images'], function (){
    Route::get('/', 'PromotionImageController@index')->name('api.promotion_images.index');
});
//перекинуть в только для авторизированных пользователей
Route::group(['namespace' => '\App\Http\Controllers\Api\Order', 'prefix' => 'orders'], function (){
//    Route::get('/', 'CategoryController@index')->name('api.categories.index');
    Route::post('/create', 'OrderController@create')->name('api.orders.create');
    Route::get('/zip_check', 'OrderController@zip_check')->name('api.orders.zip_check');
//    Route::post('/', 'UserController@index')->name('api.users.index');
});
