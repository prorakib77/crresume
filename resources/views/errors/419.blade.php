@extends('errors.layout')

@section('title', 'Page Expired')

@section('content')
<div class="error-icon rotate-animation">
    <lottie-player
        src="https://assets5.lottiefiles.com/packages/lf20_2glqwe9k.json"
        background="transparent"
        speed="1"
        style="width: 200px; height: 200px;"
        loop
        autoplay>
    </lottie-player>
</div>

<h1 class="error-title">419</h1>
<h2 class="error-subtitle">Page Expired</h2>
<p class="error-description">
    Your session has expired or the page has been idle for too long. Please refresh the page and try again.
</p>

<div class="error-actions">
    <a href="javascript:history.back()" class="btn btn-border-black">
        <i class="fas fa-arrow-left"></i>
        Go Back
    </a>
    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn btn-black">
        <i class="fas fa-home"></i>
        Go to Dashboard
    </a>
    <button onclick="window.location.reload()" class="btn btn-border-black">
        <i class="fas fa-sync-alt"></i>
        Refresh Page
    </button>
    <a href="{{ route('contact.page') }}" class="btn btn-border-black">
        <i class="fas fa-envelope"></i>
        Contact Support
    </a>
</div>
@endsection
