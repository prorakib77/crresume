@extends('errors.layout')

@section('title', 'Page Not Found')

@section('content')
<div class="error-icon floating-animation">
    <lottie-player
        src="https://assets5.lottiefiles.com/packages/lf20_ghfpce1h.json"
        background="transparent"
        speed="1"
        style="width: 200px; height: 200px;"
        loop
        autoplay>
    </lottie-player>
</div>

<h1 class="error-title">404</h1>
<h2 class="error-subtitle">Page Not Found</h2>
<p class="error-description">
    Oops! The page you're looking for doesn't exist. It might have been moved, deleted, or you entered the wrong URL.
</p>

<div class="error-actions">
    <a href="{{ url()->previous() }}" class="btn btn-border-black">
        <i class="fas fa-arrow-left"></i>
        Go Back
    </a>
    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-black">
        <i class="fas fa-home"></i>
        Go to Dashboard
    </a>
    <a href="{{ route('contact.page') }}" class="btn btn-border-black">
        <i class="fas fa-envelope"></i>
        Contact Support
    </a>
</div>
@endsection
