<?php

// Route::group(['prefix' => 'cart', 'as' => 'cart.', 'middleware' => ['cart']], function () {
//     Route::post('addProduct', 'CartController@addProduct')->name('addProduct');
//     Route::post('removeProduct', 'CartController@removeProduct')->name('removeProduct');
// });
//

Route::group(['prefix' => 'cart', 'as' => 'cart.', 'namespace' => 'App\Http\Controllers'], function () {
    Route::post('addtocart', 'CartController@addToCart')->name('addtocart');
    Route::post('removefromcart', 'CartController@removeFromCart')->name('removefromcart');
    Route::get('cart-items', 'CartController@itemsList')->name('itemsList');
    Route::get('get-cart', 'CartController@getCartData')->name('get-cart');
    Route::post('quantity', 'CartController@updateQuantity')->name('updatequantity');
});
