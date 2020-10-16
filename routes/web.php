<?php



/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['prefix' => 'v1'], function () use ($router) {
        $router->get('/key-app', function() {
            return \Illuminate\Support\Str::random(32);
        });
        $router->get('/home', ['as' => 'home.index', 'uses' => 'HomeController@index']);
        $router->get('/join', ['as' => 'join.index', 'uses' => 'JoinController@index']);
        $router->get('/partner', ['as' => 'partner.index', 'uses' => 'PartnerController@index']);
        $router->get('/service', ['as' => 'service.index', 'uses' => 'ServiceController@index']);
        $router->post('/subscribe', ['as' => 'subscribe.submit', 'uses' => 'HomeController@index']);
    });
});
