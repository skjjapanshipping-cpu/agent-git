<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/provinces','API\TambonController@getProvinces');
Route::get('/amphoes','API\TambonController@getAmphoes');
Route::get('/tambons','API\TambonController@getTambons');
Route::get('/zipcodes','API\TambonController@getZipcodes');

Route::prefix('address')->group(function () {
    Route::get('/search', 'API\AddressController@searchAddress')->name('search.address');
    Route::get('/amphoes', 'API\AddressController@getAmphoes')->name('get.amphoes');
    Route::get('/tambons', 'API\AddressController@getTambons')->name('get.tambons');
    Route::get('/provinces', 'API\AddressController@getProvinces')->name('get.provinces');
    Route::get('/zipcode', 'API\AddressController@getAddressByZipcode')->name('get.address.zipcode');
});

// Tracking API สำหรับเว็บภายนอก (จำกัด 10 ครั้ง/นาที + จำกัด domain)
Route::prefix('tracking')->middleware(['throttle:10,1', 'allowed.domains'])->group(function () {
    Route::post('/submit', 'TrackingController@submitTracking')->name('api.tracking.submit');
});

// SKJ Chat → update pay_status เมื่อสลิปตรงกับบิล (auth: X-API-Key inside controller)
Route::post('/update-pay-status', 'API\PayStatusApiController@updateFromChat')->name('api.update-pay-status');

// NOTE: /check-chat-connection ถูกย้ายไปอยู่ใน routes/web.php ภายใต้ admin only เพื่อกัน abuse
