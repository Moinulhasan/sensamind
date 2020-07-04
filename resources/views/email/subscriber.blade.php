@component('mail::message')
    Hi,<br/>
    Thanks for signing up for our mailing list. You'll be hearing from us soon enough.
    <br/>
    Regards,<br/>
    Team Sensamind
    @slot('subcopy')
        @lang(
            "If you signed up by accident, use this link to unsubscribe.".
            '[:actionURL](:actionURL)',
            [
                'actionText' => 'Unsubscribe',
                'actionURL' => $details['actionUrl'],
            ]
        )
    @endslot
@endcomponent
