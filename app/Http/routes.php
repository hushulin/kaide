<?php

use App\User;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$app->get('/', function () use ($app) {
    return $app->version();
});

$app->get('/user' , ['middleware' => 'auth:admin' , function() use ($app) {

	echo Auth::id();

	return $app->version();
}]);


// work start ...
$app->post('/user/register' , ['uses' => 'UserController@register']);
$app->post('/user/login' , ['uses' => 'UserController@login']);
$app->post('/user/logout' , ['uses' => 'UserController@logout']);
$app->post('/api/format' , ['uses' => 'MeterController@format']);

$app->post('/user/update' , ['middleware' => 'auth:admin' , 'uses' => 'UserController@update']);

$app->group(['middleware' => 'auth:admin' , 'namespace' => 'App\Http\Controllers'], function($app)
{
    $app->post('/user/money', ['uses' => 'UserController@money']);
    $app->post('/user/add-money', ['uses' => 'UserController@addMoney']);
    $app->post('/user/update-face' , ['uses' => 'UserController@updateFace']);
    $app->post('/user/default-meter-ton' , ['uses' => 'UserController@defaultMeterTon']);
    $app->post('/user/user-order-list' , ['uses' => 'UserController@UserOrderList']);

    $app->post('/meter/add' , ['uses' => 'MeterController@add']);
    $app->post('/meter/add-ton' , ['uses' => 'MeterController@addTon']);
    $app->post('/meter' , ['uses' => 'MeterController@index']);
    $app->post('/meter/set-default' , ['uses' => 'MeterController@setDefault']);
    $app->post('/meter/set-status' , ['uses' => 'MeterController@setStatus']);
    $app->post('/meter/get-by-md5' , ['uses' => 'MeterController@getByMd5']);
    $app->post('/meter/act-meter-fee' , ['uses' => 'MeterController@actMeterFee']);
    $app->post('/meter/fee-list-year' , ['uses' => 'MeterController@getFeeListByYear']);


    $app->post('/notification' , ['uses' => 'NotificationController@index']);
    $app->post('/notification/add' , ['uses' => 'NotificationController@add']);

    $app->post('/xiaofei/xflist' , ['uses' => 'XiaofeiController@xflist']);
});
//
