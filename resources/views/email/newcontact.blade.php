@component('mail::message')
    Hi,<br/>
    New user contact form request.
    Name :{{$details['name']}}<br/>
    email :{{$details['email']}}<br/>
    subject :{{$details['subject']}}<br/>
    message :{{$details['message']}}<br/>
    <br/><br/><br/>
    Regards,<br/>
    Team Sensamind
@endcomponent
