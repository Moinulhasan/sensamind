@component('mail::message')
    Hi {{$details['name']}},<br/>
    Your account is locked due to too many failed login attempts.<br/>
    Please Click link below to unlock your account and regain access:
    <br/>
    @component('mail::button', ['url' => $details['actionUrl'], 'color' =>'red'])
        Unlock My Account
    @endcomponent

    Regards,<br/>
    Team Sensamind

    @slot('subcopy')
        @lang(
            "If you’re having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
            'into your web browser: [:actionURL](:actionURL)',
            [
                'actionText' => 'Unlock My Account',
                'actionURL' => $details['actionUrl'],
            ]
        )
    @endslot
@endcomponent
