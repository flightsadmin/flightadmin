@component('mail::message')
    {!! $content !!}

    @component('mail::subcopy')
        This is an automated message. Please do not reply to this email.
    @endcomponent
@endcomponent
