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

    $api->group(['prefix'=>'admin','middleware' => ['jwt.auth','auth.role:admin']], function(Router $api) {
        $api->get('user/list', 'App\\Api\\V1\\Controllers\\UserController@allUsers');
        $api->get('labels', 'App\\Api\\V1\\Controllers\\LabelsController@getLabels');
        $api->post('labels', 'App\\Api\\V1\\Controllers\\LabelsController@createLabel');
        $api->put('labels', 'App\\Api\\V1\\Controllers\\LabelsController@updateLabel');
    });

    $api->group(['prefix' => 'user', 'middleware' => ['jwt.auth','auth.role:user']], function (Router $api) {
        $api->post('click', 'App\\Api\\V1\\Controllers\\UserController@setClicks');
    });

    $api->group(['prefix' => 'user', 'middleware' => 'jwt.auth'], function (Router $api) {
        $api->get('me', 'App\\Api\\V1\\Controllers\\UserController@me');
        $api->get('clicks', 'App\\Api\\V1\\Controllers\\UserController@getClicks');
        $api->get('statistics', 'App\\Api\\V1\\Controllers\\UserController@getMyStatistics');
        $api->put('/update', 'App\\Api\\V1\\Controllers\\UserController@updateUserDetails');

        $api->get('refresh', [
            'middleware' => 'jwt.refresh',
            function() {
                return response()->json([
                    'success' => true,
                    'message' => 'Token Refresh'
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
