<!DOCTYPE html>
<html lang="en">
<head>
    @php($siteFavicon = site_favicon())
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OAuth Test - {{ site_name() }}</title>

    <!-- Dynamic Favicon -->
    @if($siteFavicon)
        <link rel="icon" type="image/x-icon" href="{{ $siteFavicon }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $siteFavicon }}">
    @endif

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite('resources/css/app.css')
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">🔐 Google OAuth Test</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h5>📋 OAuth Configuration Test</h5>
                            <p>This page helps you test the Google OAuth configuration and fix any issues.</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h6>🔧 Configuration Details:</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item">
                                        <strong>Client ID:</strong><br>
                                        <code>{{ config('services.google.client_id') }}</code>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>Redirect URI:</strong><br>
                                        <code>{{ config('services.google.redirect_uri') }}</code>
                                    </li>
                                    <li class="list-group-item">
                                        <strong>API Key:</strong><br>
                                        <code>{{ config('services.google.meet_api_key') }}</code>
                                    </li>
                                </ul>
                            </div>

                            <div class="col-md-6">
                                <h6>🚀 Test OAuth Flow:</h6>
                                <div class="d-grid gap-2">
                                    <a href="{{ $authUrl }}" class="btn btn-primary btn-lg">
                                        <i class="fab fa-google me-2"></i>Test Google OAuth
                                    </a>
                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                                    </a>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="alert alert-warning">
                            <h6>⚠️ If you see "access_denied" error:</h6>
                            <ol>
                                <li><strong>Go to Google Cloud Console:</strong> <a href="https://console.cloud.google.com/" target="_blank">https://console.cloud.google.com/</a></li>
                                <li><strong>Select your project:</strong> wfh-meet-473418</li>
                                <li><strong>Go to:</strong> "APIs & Services" → "OAuth consent screen"</li>
                                <li><strong>Click:</strong> "Test users" tab</li>
                                <li><strong>Add your email:</strong> itabdullahm.trakib@gmail.com</li>
                                <li><strong>Save</strong> and try again</li>
                            </ol>
                        </div>

                        <div class="alert alert-success">
                            <h6>✅ Alternative Solution:</h6>
                            <p>You can also publish the app to make it available to all users:</p>
                            <ol>
                                <li>Go to "OAuth consent screen"</li>
                                <li>Click "Publish app"</li>
                                <li>Confirm the publishing</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
