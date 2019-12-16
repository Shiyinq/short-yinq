<?php

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

$router->get('/', function () use ($router) {
  return $router->app->version();
});

$router->post('/login', [
	'as' => 'login',
	'uses' => 'UserController@login'
]);

$router->post('/register', [
	'as' => 'register', 
	'uses' => 'UserController@register'	
]);

$router->post('/shortyinq', [
	'as' => 'short-link', 
	'uses' => 'ShortYinqController@automaticShortLink'
	]
);

$router->get('/{url}', [
	'as' => 'redirect',
	'uses' => 'ShortYinqController@redirectLink'
]);
