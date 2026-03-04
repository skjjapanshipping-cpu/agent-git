<?php
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
use App\Tracker;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\InvoiceController;
Route::get('/', function () {

    //number of user connected or viewed
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    Tracker::firstOrCreate([
        'ip'   => $ip],
        ['ip'   => $ip,
            'current_date' => date('Y-m-d')])->save();

    return redirect()->route('home');

})->name('welcome');

Route::get('tracking','TrackingController@index');
Route::post('/submit-tracking', 'TrackingController@submitTracking')->name('submit-tracking');
Route::group(['middleware' => ['role:admin']], function () {
    //destination_date
    Route::post('/update-status', 'TrackController@update_StatusByIDs')->name('update-status');
    //ship_date
    Route::post('/update-status2', 'TrackController@update_StatusByIDs2')->name('update-status2');

    //shipping_status customerorder
    Route::post('/update-status-shipping', 'CustomerorderController@update_StatusByIDs')->name('update-status-shipping');
    Route::post('/update-status-pay', 'CustomerorderController@update_StatusByIDs2')->name('update-status-pay');
    Route::post('/update-status-supplier-pay', 'CustomerorderController@update_SupplierStatusByIDs')->name('update-status-supplier-pay');

    //shipping_status customershipping
    Route::post('/update-status-shipping2', 'CustomershippingController@update_StatusByIDs')->name('update-status-shipping2');
    Route::post('/update-status-pay2', 'CustomershippingController@update_StatusByIDs2')->name('update-status-pay2');
    Route::post('/update-status-received2', 'CustomershippingController@update_StatusByIDs3')->name('update-status-received2');

    // LINE notification
    Route::post('/send-line-notification', 'CustomershippingController@sendLineNotification')->name('send.line.notification');

    Route::resource('permissions', 'Admin\PermissionsController');
    Route::resource('roles', 'Admin\RolesController');
    Route::resource('users', 'Admin\UsersController');
    Route::get('users/{user}/impersonate', 'Admin\UsersController@impersonate')->name('users.impersonate');
    Route::get('login-activities',[
        'as' => 'login-activities',
        'uses' => 'Admin\UsersController@indexLoginLogs'
    ]);

    Route::resource('dailyrates', 'DailyrateController');
    Route::resource('categories', 'CategoryController');
    Route::resource('customerorders', 'CustomerorderController');
    Route::resource('purchase-requests', 'PurchaseRequestController');
    Route::post('fetch-purchase-requests', 'PurchaseRequestController@fetch')->name('fetch.purchase-requests');
    Route::post('purchase-requests/bulk-status', 'PurchaseRequestController@bulkUpdateStatus')->name('purchase-requests.bulk-status');
    Route::resource('customers', 'CustomerController');
    Route::resource('pay-status', 'PayStatusController');
    Route::resource('shipping-status', 'ShippingStatusController');

    Route::get('/clear-cache', function () {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return "Cache cleared successfully";
    });
});

//shipping_type customershippingView (user role)
Route::middleware('auth')->post('/update-delivery-type', 'CustomerShippingViewController@update_StatusByIDs')->name('update-delivery-type');


Route::group(['middleware' => ['auth']], function () {
    Route::get('stop-impersonate', 'Admin\UsersController@stopImpersonate')->name('users.stop-impersonate');
    Route::get('/clear-session', 'CustomerShippingViewController@clearSession')->name('clear.session');



    Route::resource('profile','Users\ProfileController');
    Route::get('change-password', 'Users\ProfileController@showChangePassword')->name('change-password');
    Route::post('change-password', 'Users\ProfileController@changePassword')->name('change-password.update');

    Route::get('shipment-analytics', 'CustomerShippingViewController@analytics')->name('shipment-analytics');

    Route::resource('apps', 'AppController');

    Route::resource('locations', 'LocationController',['except'=>['create','index']]);
    Route::get('locations/create/{app_id?}',[ 'as'=>'locations.create','uses'=>'LocationController@create']);
    Route::get('locations/homelists/{app_id?}',[ 'as'=>'locations.index','uses'=>'LocationController@index']);

    Route::resource('tracks', 'TrackController');
    //confirm import
    Route::post('/update-confirmimport', 'TrackController@update_confirmimport')->name('update-confirmimport');
    Route::post('/del-confirmimport', 'TrackController@del_confirmimport')->name('del-confirmimport');
    Route::post('/updatecustomershippings-confirmimport', 'CustomershippingController@update_confirmimport')->name('updatecustomershippings-confirmimport');
    Route::post('/delcustomershippings-confirmimport', 'CustomershippingController@del_confirmimport')->name('delcustomershippings-confirmimport');

    Route::get('tracksconfirm', 'TrackController@confirmImport')->name('tracksconfirm');
    Route::get('tracksimport', 'TrackController@importView')->name('tracksimport');

    Route::post('import', 'TrackController@import')->name('import');

    Route::get('orderview', 'CustomerviewController@index')->name('orderview.index');
    Route::post('fetchcustomerorderview', 'CustomerviewController@fetchCustomerorder')->name('fetch.customerorderview');

    Route::resource('customershippings', 'CustomershippingController');

    Route::get('customershippingsconfirm', 'CustomershippingController@confirmImport')->name('customershippingsconfirm');
    Route::get('customershippingsimport', 'CustomershippingController@importView')->name('customershippingsimport');
    Route::post('callcustomershippingsimport', 'CustomershippingController@import')->name('callcustomershippingsimport');
    Route::get('customershippingsexport/{start_date?}', 'CustomershippingController@export')->name('customershippingsexport');
    Route::get('customershippingsexport2/{start_date?}', 'CustomershippingController@export2')->name('customershippingsexport2');

    //frontend export
    Route::get('customershippingsviewexport2/{customerno?}/{start_date?}', 'CustomerShippingViewController@export2')->name('customershippingsviewexport2');

    Route::get('customershippingview/{customershipping}/edit', 'CustomerShippingViewController@edit')->name('customershippingview.edit');
//    Route::put('customershippingview/{customershipping}', 'CustomerShippingViewController@update')->name('customershippingview.update');
    Route::match(['put', 'patch'], 'customershippingview/{customershipping}', 'CustomerShippingViewController@update')->name('customershippingview.update');
    Route::get('shippingview', 'CustomerShippingViewController@index')->name('shippingview.index');
    Route::post('fetchcustomershippings', 'CustomershippingController@fetchCustomershippings')->name('fetch.customershippings');
    Route::post('fetchcustomershippingsview', 'CustomerShippingViewController@fetchCustomershippingsview')->name('fetch.customershippingsview');
    Route::post('fetchcustomerorder', 'CustomerorderController@fetchCustomerorder')->name('fetch.customerorder');
    Route::get('customerorderexport2', 'CustomerorderController@export2')->name('customerorderexport2');

Route::get('/get-new-itemno', 'CustomerorderController@getNewItemno');
Route::get('/get-available-itemno', 'CustomerorderController@getAvailableItemno');
Route::get('/check-itemno-exists', 'CustomerorderController@checkItemnoExists');
Route::post('/check-customerorder-exists', 'CustomerorderController@checkCustomerorderExists');
Route::post('/check-existing-itemnos', 'CustomershippingController@checkExistingItemnos');




    Route::post('fetchtrack', 'TrackController@fetchTrack')->name('fetch.track');


});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/auth/line', 'Auth\LoginController@redirectToLine');
//Route::get('/callback', 'Auth\LoginController@handleLineCallback');
Route::get('/auth/line/callback', 'Auth\LoginController@handleLineCallback');


Route::middleware('auth')->group(function () {
    Route::get('/invoice/{etd}/{customerno}/{shipping_ids?}', [InvoiceController::class, 'generateInvoice'])->name('invoice.generate');
    // เพิ่ม route สำหรับ customerorder invoice
    Route::get('invoice-order/{order_date}/{end_order_date}/{status}/{customerorderids}/{customerno}', [InvoiceController::class, 'generateOrderInvoice'])->name('invoice.order');
    // ส่งบิลผ่าน SKJ Chat
    Route::post('/send-invoice-chat', [InvoiceController::class, 'sendInvoiceChat'])->name('send.invoice.chat');
    Route::post('/check-chat-connection', [InvoiceController::class, 'checkChatConnection'])->name('check.chat.connection');
    Route::post('/remind-payment', [InvoiceController::class, 'remindPayment'])->name('remind.payment');
});


Route::middleware('auth')->get('/api/address/searchCustomerAddress', 'API\AddressController@searchCustomerAddress')
    ->name('search.customer.address');
    
Route::middleware('auth')->get('/api/address/searchCustomerShippingAddress', 'API\AddressController@searchCustomerShippingAddress')
    ->name('search.customer.shipping.address');

Route::middleware('auth')->get('/get-customer-delivery-type', 'CustomershippingController@getCustomerDeliveryType');

// Price Calculator (public)
Route::get('/calc', 'PriceCalculatorController@index')->name('price.calculator');
Route::post('/api/scrape-product', 'PriceCalculatorController@scrapeProduct')->name('api.scrape.product');

// Scanner Login (separate for warehouse staff)
Route::get('/scanner/login', 'ScannerAuthController@showLogin')->name('scanner.login');
Route::post('/scanner/login', 'ScannerAuthController@login');
Route::post('/scanner/logout', 'ScannerAuthController@logout')->name('scanner.logout');

// Scanner Home (scanner + admin role)
Route::middleware(['auth', 'role:scanner|admin'])->group(function () {
    Route::get('/scanner', 'QrScanController@scannerHome')->name('scanner.home');
    Route::get('/scanner/pickup', 'QrScanController@pickupHome')->name('scanner.pickup');
});

// QR Scan API (accessible by both admin and scanner)
Route::middleware('auth')->group(function () {
    Route::get('/qr-scan/api/box/{box_no}', 'QrScanController@getBoxInfo')->name('qrscan.api.box');
    Route::post('/qr-scan/api/update-status', 'QrScanController@updateBoxStatus')->name('qrscan.api.update-status');
    Route::post('/qr-scan/api/clear-scan', 'QrScanController@clearScan')->name('qrscan.api.clear-scan');
    // Pickup API
    Route::get('/qr-scan/api/pickup/rounds', 'QrScanController@getAvailableRounds')->name('qrscan.api.pickup-rounds');
    Route::get('/qr-scan/api/pickup/customers', 'QrScanController@getPickupCustomers')->name('qrscan.api.pickup-customers');
    Route::get('/qr-scan/api/pickup/customer/{customerno}', 'QrScanController@getCustomerParcels')->name('qrscan.api.pickup-customer');
    Route::post('/qr-scan/api/pickup/scan', 'QrScanController@pickupScan')->name('qrscan.api.pickup-scan');
});

// Scan History (admin)
Route::middleware(['auth', 'role:admin'])->get('/scan-history', 'QrScanController@scanHistory')->name('scan-history');
Route::middleware(['auth', 'role:admin'])->get('/scan-history/data', 'QrScanController@scanHistoryData')->name('scan-history.data');

// QR Code Scanner System (admin only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/qr-scan/scanner', 'QrScanController@scanner')->name('qrscan.scanner');
    Route::get('/qr-scan/print-labels', 'QrScanController@printLabels')->name('qrscan.print-labels');
    Route::post('/qr-scan/print-labels/save', 'QrScanController@saveLabelsCounter')->name('qrscan.save-labels-counter');
    Route::get('/qr-scan/print-etd/{etd}', 'QrScanController@printByEtd')->name('qrscan.print-etd');
    Route::get('/qr-scan/print/{box_no}', 'QrScanController@printQr')->name('qrscan.print');
    Route::post('/qr-scan/generate', 'QrScanController@generateBoxNumbers')->name('qrscan.generate');
    Route::get('/qr-scan/result/{box_no}', 'QrScanController@scanResult')->name('qrscan.result');
});

