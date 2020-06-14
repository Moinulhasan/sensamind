<?php

return [

    // these options are related to the sign-up procedure
    'sign_up' => [

        // this option must be set to true if you want to release a token
        // when your user successfully terminates the sign-in procedure
        'release_token' => env('SIGN_UP_RELEASE_TOKEN', false),

        // here you can specify some validation rules for your sign-in request
        'validation_rules' => [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]
    ],

    // these options are related to the login procedure
    'login' => [

        // here you can specify some validation rules for your login request
        'validation_rules' => [
            'email' => 'required|email',
            'password' => 'required'
        ]
    ],

    // these options are related to the password recovery procedure
    'forgot_password' => [

        // here you can specify some validation rules for your password recovery procedure
        'validation_rules' => [
            'email' => 'required|email'
        ]
    ],

    // these options are related to the password recovery procedure
    'reset_password' => [

        // this option must be set to true if you want to release a token
        // when your user successfully terminates the password reset procedure
        'release_token' => env('PASSWORD_RESET_RELEASE_TOKEN', false),

        // here you can specify some validation rules for your password recovery procedure
        'validation_rules' => [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed'
        ]
    ],

    // these options are related to the account verification procedure
    'verify_account' => [

        // this option must be set to true if you want to release a token
        // when user successfully verifies the account
        'release_token' => env('PASSWORD_RESET_RELEASE_TOKEN', false),

        // here you can specify some validation rules for your password recovery procedure
        'validation_rules' => [
            'verification_code' => 'required',
        ]
    ],

    'clicks' => [
        'validation_rules' => [
            'clicks' => 'required|array',
            'current_set.*' => 'required|numeric',
            'button.*' => 'required|numeric|min:1|max:2',
            'cause.*' => 'required|numeric|min:1|max:5',
            'clicked_at.*' => 'required|date'
        ]
    ]

];
