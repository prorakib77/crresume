<x-app-layout>
    <x-slot name="title">Google OAuth Setup Guide</x-slot>
    <x-slot name="pageTitle">Google OAuth Setup Guide</x-slot>
    <x-slot name="pageSubtitle">Step-by-step guide to set up Google Service Account credentials</x-slot>

    <div class="container-fluid">
        <!-- Error Alert -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="alert alert-danger">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Google Project Deleted</h5>
                    <p class="mb-2">Your current Google Cloud Project (#357339854675) has been deleted. You need to create new credentials.</p>
                    <p class="mb-0">Follow the steps below to set up a new Google Cloud Project and service account.</p>
                </div>
            </div>
        </div>

        <!-- Step 1: Create Google Cloud Project -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-1 me-2"></i>Step 1: Create Google Cloud Project
                        </h5>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li><strong>Go to Google Cloud Console:</strong> <a href="https://console.cloud.google.com/" target="_blank" class="text-decoration-none">https://console.cloud.google.com/</a></li>
                            <li><strong>Create a new project:</strong>
                                <ul>
                                    <li>Click "Select a project" at the top</li>
                                    <li>Click "New Project"</li>
                                    <li>Enter project name: "W Automation Meet System"</li>
                                    <li>Click "Create"</li>
                                </ul>
                            </li>
                            <li><strong>Select the new project</strong> from the project dropdown</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Enable APIs -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-2 me-2"></i>Step 2: Enable Required APIs
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Enable the following APIs in your Google Cloud project:</p>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Google Calendar API</h6>
                                <ol>
                                    <li>Go to <a href="https://console.cloud.google.com/apis/library/calendar-json.googleapis.com" target="_blank">Google Calendar API</a></li>
                                    <li>Click "Enable"</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h6>Google Meet API (Optional)</h6>
                                <ol>
                                    <li>Go to <a href="https://console.cloud.google.com/apis/library/meet.googleapis.com" target="_blank">Google Meet API</a></li>
                                    <li>Click "Enable"</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3: Create Service Account -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-3 me-2"></i>Step 3: Create Service Account
                        </h5>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li><strong>Go to Service Accounts:</strong> <a href="https://console.cloud.google.com/iam-admin/serviceaccounts" target="_blank">https://console.cloud.google.com/iam-admin/serviceaccounts</a></li>
                            <li><strong>Create Service Account:</strong>
                                <ul>
                                    <li>Click "Create Service Account"</li>
                                    <li>Name: "W Automation Service Account"</li>
                                    <li>Description: "Service account for W Automation meeting system"</li>
                                    <li>Click "Create and Continue"</li>
                                </ul>
                            </li>
                            <li><strong>Grant Roles:</strong>
                                <ul>
                                    <li>Add role: "Calendar Editor"</li>
                                    <li>Add role: "Service Account User"</li>
                                    <li>Click "Continue"</li>
                                </ul>
                            </li>
                            <li><strong>Click "Done"</strong></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 4: Create and Download Credentials -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-4 me-2"></i>Step 4: Create and Download Credentials
                        </h5>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li><strong>Click on your service account</strong> in the list</li>
                            <li><strong>Go to "Keys" tab</strong></li>
                            <li><strong>Add Key:</strong>
                                <ul>
                                    <li>Click "Add Key" → "Create new key"</li>
                                    <li>Select "JSON" format</li>
                                    <li>Click "Create"</li>
                                    <li>Download the JSON file</li>
                                </ul>
                            </li>
                            <li><strong>Keep the JSON file secure</strong> - it contains sensitive credentials</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 5: Configure OAuth Settings -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-black text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-5 me-2"></i>Step 5: Configure OAuth Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Upload Credentials</h6>
                                <ol>
                                    <li>Go to <a href="{{ route('admin.oauth.create') }}" class="text-decoration-none">OAuth Settings</a></li>
                                    <li>Upload the downloaded JSON file</li>
                                    <li>Or paste the JSON content directly</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <h6>Configure Settings</h6>
                                <ul>
                                    <li><strong>Admin Email:</strong> Your Gmail address</li>
                                    <li><strong>Calendar ID:</strong> "primary" (for main calendar)</li>
                                    <li><strong>Timezone:</strong> "Asia/Dhaka"</li>
                                    <li><strong>Enable OAuth:</strong> Check this box</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 6: Test Connection -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-6 me-2"></i>Step 6: Test Connection
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Test Your Setup</h6>
                                <ol>
                                    <li>Save your OAuth settings</li>
                                    <li>Click "Test Connection" button</li>
                                    <li>Verify successful connection</li>
                                    <li>Generate a test meeting</li>
                                </ol>
                            </div>
                            <div class="col-md-4">
                                <div class="d-grid gap-2">
                                    <a href="{{ route('admin.oauth.create') }}" class="btn btn-primary">
                                        <i class="fas fa-cog me-2"></i>Configure OAuth
                                    </a>
                                    <a href="{{ route('admin.generate-meeting') }}" class="btn btn-success">
                                        <i class="fas fa-video me-2"></i>Test Meeting
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Important Notes -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>Important Notes
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Security</h6>
                                <ul>
                                    <li>Keep your JSON credentials file secure</li>
                                    <li>Don't share credentials publicly</li>
                                    <li>Use environment variables in production</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Troubleshooting</h6>
                                <ul>
                                    <li>Ensure APIs are enabled</li>
                                    <li>Check service account permissions</li>
                                    <li>Verify calendar access</li>
                                    <li>Test with a simple calendar event first</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
