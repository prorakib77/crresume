@extends('errors.layout')

@section('title', 'Server Error')

@section('content')
<div class="error-icon pulse-animation">
    <lottie-player
        src="https://assets5.lottiefiles.com/packages/lf20_8wRE4I.json"
        background="transparent"
        speed="1"
        style="width: 200px; height: 200px;"
        loop
        autoplay>
    </lottie-player>
</div>

<h1 class="error-title">500</h1>
<h2 class="error-subtitle">Internal Server Error</h2>
<p class="error-description">
    Something went wrong on our end. We're working to fix this issue. Please try again later or contact support if the problem persists.
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
