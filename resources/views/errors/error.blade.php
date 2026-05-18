@extends('errors.layout')

@section('title', 'Error')

@section('content')
<div class="error-icon bounce-animation">
    <lottie-player
        src="https://assets5.lottiefiles.com/packages/lf20_8wRE4I.json"
        background="transparent"
        speed="1"
        style="width: 200px; height: 200px;"
        loop
        autoplay>
    </lottie-player>
</div>

<h1 class="error-title">{{ $exception->getStatusCode() ?? 'Error' }}</h1>
<h2 class="error-subtitle">{{ $exception->getMessage() ?: 'Something went wrong' }}</h2>
<p class="error-description">
    An unexpected error occurred. Please try again or contact support if the problem persists.
</p>

<div class="error-actions">
    <a href="{{ url()->previous() }}" class="btn btn-border-black">
        <i class="fas fa-arrow-left"></i>
        Go Back
    </a>
    <a href="{{ route('dashboard') }}" class="btn btn-black">
        <i class="fas fa-home"></i>
        Go to Dashboard
    </a>
    <a href="mailto:support@{{ parse_url(config('app.url'), PHP_URL_HOST) }}" class="btn btn-border-black">
        <i class="fas fa-envelope"></i>
        Contact Support
    </a>
</div>
@endsection
