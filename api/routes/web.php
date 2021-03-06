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

$router->get('/{url}', [
	'as' => 'redirect',
	'uses' => 'ShortYinqController@redirectLink'
]);

$router->group(['prefix' => 'api/v1/'], function () use ($router) {
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
		'uses' => 'ShortYinqController@automaticShortenerURL'
		]
	);

	// after login
	$router->group(['middleware' => 'auth'], function () use ($router) {
		$router->post('/shortyinq/custom', [
			'as' => 'short-link-custom', 
			'uses' => 'ShortYinqController@customShortenerURL'
			]
		);
		
		$router->get('/urls', [
			'as' => 'urls',
			'uses' => 'ShortYinqController@listURL'
		]);
		
		$router->get('/delete/{id}', [
			'as' => 'delete',
			'uses' => 'ShortYinqController@deleteURL'
		]);

	});

});