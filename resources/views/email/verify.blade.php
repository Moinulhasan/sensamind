@component('mail::message')
    Hi {{$details['name']}},<br/>
    Thank you for creating an account with us. There's just one more step:
    <br/>
    @component('mail::button', ['url' => $details['actionUrl']])
        Verify My Account
    @endcomponent
    Regards,<br/>
    Team Puramind
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
