@extends('errors.layout')

@section('title', 'Access Forbidden')

@section('content')
<div class="error-icon shake-animation">
    <lottie-player
        src="https://assets5.lottiefiles.com/packages/lf20_1pxqjqps.json"
        background="transparent"
        speed="1"
        style="width: 200px; height: 200px;"
        loop
        autoplay>
    </lottie-player>
</div>

<h1 class="error-title">403</h1>
<h2 class="error-subtitle">Access Forbidden</h2>
<p class="error-description">
    You don't have permission to access this resource. Please check your credentials or contact an administrator if you believe this is an error.
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
    @guest
    <a href="{{ route('login') }}" class="btn btn-border-black">
        <i class="fas fa-sign-in-alt"></i>
        Login
    </a>
    @endguest
</div>
@endsection
