@component('mail::message')
# New Contact Message Received

You have received a new contact message from {{ $contactMessage->name }}.

## Message Details

**Name:** {{ $contactMessage->name }}
**Email:** {{ $contactMessage->email }}
**Subject:** {{ $contactMessage->subject }}
**Date:** {{ $contactMessage->created_at->format('F j, Y \a\t g:i A') }}

## Message Content

{{ $contactMessage->content }}

---

@component('mail::button', ['url' => config('app.url') . '/admin/contact-messages'])
View in Admin Panel
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
