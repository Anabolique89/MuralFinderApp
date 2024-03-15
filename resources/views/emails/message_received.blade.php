@component('mail::message')
# New Message Received
====================

Hello Admin,

You have received a new message from a user:

- Name: <b>{{ $message->name }}</b>
- Email: <b>{{ $message->email }}</b>

@component('mail::panel')
### Message 
{{ $message->content }}
@endcomponent


@component('mail::button', ['url' => 'mailto:' . $message->email])
Reply Here
@endcomponent

Thanks,  
{{ config('app.name') }} Team.  
&copy; {{ date('Y') }}
@endcomponent
