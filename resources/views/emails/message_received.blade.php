@component('mail::message')
# New Message Received

Hello Admin,

You have received a new message from a user:

@component('mail:panel')
Subject: ## {{ $message->subject}}
Name: ## {{ $message->name }}
Email: ## {{ $message->email }}

## Message 
{{ $message->content }}
@endcomponent


@component('mail::button', ['url' => 'mailto:' . $message->email])
Reply Here
@endcomponent

Thanks,  
{{ config('app.name') }} Team.  
&copy; {{ date('Y') }}
@endcomponent
