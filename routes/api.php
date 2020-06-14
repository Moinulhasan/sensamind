<?php

use Dingo\Api\Routing\Router;

/** @var Router $api */
$api = app(Router::class);

$api->version('v1', function (Router $api) {
    $api->group(['prefix' => 'auth'], function(Router $api) {
        $api->post('signup', 'App\\Api\\V1\\Controllers\\SignUpController@signUp');
        $api->post('signin', 'App\\Api\\V1\\Controllers\\LoginController@login');

        $api->post('forgot-password', 'App\\Api\\V1\\Controllers\\ForgotPasswordController@sendResetEmail');
        $api->post('reset-password', 'App\\Api\\V1\\Controllers\\ResetPasswordController@resetPassword');

        $api->post('logout', 'App\\Api\\V1\\Controllers\\LogoutController@logout');
        $api->post('refresh', 'App\\Api\\V1\\Controllers\\RefreshController@refresh');
        $api->post('verify-account', 'App\\Api\\V1\\Controllers\\AccountController@verifyAccount');
        $api->post('unlock-account', 'App\\Api\\V1\\Controllers\\AccountController@unlockAccount');
    });

    $api->group(['prefix'=>'admin/user','middleware' => ['jwt.auth','auth.role:admin']], function(Router $api) {
        $api->get('list', 'App\\Api\\V1\\Controllers\\UserController@allUsers');
        $api->get('labels', 'App\\Api\\V1\\Controllers\\LabelsController@getLabels');
        $api->post('labels', 'App\\Api\\V1\\Controllers\\LabelsController@createLabel');
        $api->put('labels', 'App\\Api\\V1\\Controllers\\LabelsController@updateLabel');
    });

    $api->group(['prefix' => 'user', 'middleware' => ['jwt.auth','auth.role:user']], function (Router $api) {
        $api->post('save-click', 'App\\Api\\V1\\Controllers\\UserController@setClicks');
        $api->get('get-clicks', 'App\\Api\\V1\\Controllers\\UserController@getClicks');
    });

    $api->group(['prefix' => 'user', 'middleware' => 'jwt.auth'], function (Router $api) {
        $api->get('me', 'App\\Api\\V1\\Controllers\\UserController@me');

        $api->get('refresh', [
            'middleware' => 'jwt.refresh',
            function() {
                return response()->json([
                    'message' => 'By accessing this endpoint, you can refresh your access token at each request. Check out this response headers!'
                ]);
            }
        ]);
    });

    $api->get('hello', function() {
        return response()->json([
            'message' => 'This is a simple example of item returned by your APIs. Everyone can see it.'
        ]);
    });
});
