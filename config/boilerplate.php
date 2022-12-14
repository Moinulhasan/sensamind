<?php

use App\Rules\IsValidPassword;

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
//            'password' => [
//                'required',
//                'confirmed',
//                'string',
//                new IsValidPassword()
//            ]
        ]
    ],

    'create_user' => [
        'validation_rules' => [
            'name' => 'required',
            'email' => 'required|email',
            'age' => 'required|numeric',
            'gender' => 'required|in:0,1,2',
            'role' => 'required|in:user,admin,super_admin',
            'user_group' => 'required|numeric'
        ]
    ],

    'create_user_group' => [
        'validation_rules' => [
            'name' => 'required',
            'description' => 'required'
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
            'password' => [
                'required',
                'confirmed',
                'string',
                new IsValidPassword()
            ],
            'email' => 'required'
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
            'clicks.*.button_id' => 'required',
            'clicks.*.button' => 'required',
            'clicks.*.cause' => 'required',
            'clicks.*.clicked_at' => 'required|date'
        ]
    ],

    'bluetooth_clicks' => [
        'validation_rules' => [
            'clicks' => 'required|array',
            'clicks.*.button_id' => 'required',
            'clicks.*.button' => 'required',
            'clicks.*.clicked_at' => 'required|date'
        ]
    ],

    'get_user_clicks' => [
        'validation_rules' => [
            'start_date' => 'date|required_with:end_date',
            'end_date' => 'date|required_with:start_date'
        ]
    ],

    'by_id' => [
        'validation_rules' => [
            'id' => 'required',
        ]
    ],

    'contact_form' => [
        'validation_rules' => [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'subject' => 'required|min:5',
            'message' => 'required|min:10'
        ]
    ],

    'mailing_list' => [
        'validation_rules' => [
            'email' => 'required|email'
        ]
    ],

    'notification_token' => [
        'validation_rules' => [
            'registration_id' => 'required',
            'type' => 'required'
        ]
    ],

    'notification_message' => [
        'validation_rules' => [
            'title' => 'required|min:3',
            'body' => 'required|min:5'
        ]
    ]

];
