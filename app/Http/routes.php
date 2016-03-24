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

$app->get('/v' , function(){
    $user = User::all();
    var_dump($user);
});

$app->get('/user' , ['middleware' => 'auth:api' , function() {
	return $app->version();
}]);
