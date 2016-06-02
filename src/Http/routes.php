<?php

Route::group(['middleware' => 'web', 'prefix' => 'checkout', 'namespace' => 'Bozboz\Ecommerce\Payment\Http\Controllers'], function()
{
    Route::get('sagepay-spoof', [
        'as' => 'checkout.sagepay-spoof',
        'uses' => 'SagepaySpoofController@index'
    ]);

    Route::post('sagepay-spoof', [
        'as' => 'checkout.sagepay-spoof-notify',
        'uses' => 'SagepaySpoofController@notify'
    ]);
});
