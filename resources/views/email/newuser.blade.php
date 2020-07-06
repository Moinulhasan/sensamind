@component('mail::message')
    Hi {{$details['name']}},<br/>
    Your Sensamind account is just a step away from ready to use. Find below username and password.
    <br/>
    <strong>Username :</strong>{{$details['email']}}<br/>
    <strong>Password :</strong>{{$details['password']}}
    <br/><br/>
    @component('mail::button', ['url' => $details['actionUrl']])
        Verify My Account
    @endcomponent

    Team Sensamind

    @slot('subcopy')
        @lang(
            "If you’re having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
            'into your web browser: [:actionURL](:actionURL)',
            [
                'actionText' => 'Verify My Account',
                'actionURL' => $details['actionUrl'],
            ]
        )
    @endslot
@endcomponent
