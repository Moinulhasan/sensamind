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

        $api->get('refresh', ['middleware' => 'jwt.refresh',function() {
            return response()->json([
                'success' => true,
                'message' => 'Token Refreshed'
            ]);
        }
        ]);
    });

    $api->group(['prefix'=>'admin','middleware' => ['jwt.auth','auth.role:admin']], function(Router $api) {
        $api->get('buttons', 'App\\Api\\V1\\Controllers\\ButtonsController@getButtons');
        $api->get('user_groups', 'App\\Api\\V1\\Controllers\\UserGroupController@getUserGroups');
        $api->post('user_groups', 'App\\Api\\V1\\Controllers\\UserGroupController@createUserGroup');
        $api->put('user_groups', 'App\\Api\\V1\\Controllers\\UserGroupController@updateUserGroup');
        $api->put('buttons', 'App\\Api\\V1\\Controllers\\ButtonsController@updateButton');
        $api->get('users', 'App\\Api\\V1\\Controllers\\UserController@allUsers');
        $api->post('users', 'App\\Api\\V1\\Controllers\\SignUpController@createUser');
    });

    $api->group(['prefix' => 'user', 'middleware' => ['jwt.auth','auth.role:user']], function (Router $api) {
        $api->post('bluetooth/click', 'App\\Api\\V1\\Controllers\\UserController@setBluetoothClicks');
    });

    $api->group(['prefix' => 'user', 'middleware' => 'jwt.auth'], function (Router $api) {
        $api->put('/', 'App\\Api\\V1\\Controllers\\UserController@updateUserDetails');
        $api->get('/', 'App\\Api\\V1\\Controllers\\UserController@userDetail');
        $api->post('click', 'App\\Api\\V1\\Controllers\\UserController@setClicks');
        $api->get('clicks', 'App\\Api\\V1\\Controllers\\UserController@getClicks');
        $api->get('statistics', 'App\\Api\\V1\\Controllers\\UserController@getMyStatistics');
        $api->get('bluetooth/statistics', 'App\\Api\\V1\\Controllers\\UserController@getBluetoothClickStats');
    });

    $api->group(['prefix' => 'contact'], function(Router $api) {
        $api->post('subscribe', 'App\\Api\\V1\\Controllers\\ContactsController@subscribe');
        $api->post('unsubscribe', 'App\\Api\\V1\\Controllers\\ContactsController@unSubscribe');
        $api->post('/', 'App\\Api\\V1\\Controllers\\ContactsController@contactDetails');
    });

});
