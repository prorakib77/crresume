@extends('layouts.bootstrap_master')

@section('title', 'System Settings')
@section('page-title', 'System Settings')
@section('page-subtitle', 'Configure your application')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                <i class="fas fa-cog text-white"></i>
            </div>
            <div>
                <h1 class="h3 mb-0">System Settings</h1>
                <small class="text-muted">Manage your application configuration</small>
            </div>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('settings.export') }}" class="btn btn-outline-secondary">
                <i class="fas fa-download me-2"></i>Export
            </a>
            <button onclick="showImportModal()" class="btn btn-secondary">
                <i class="fas fa-upload me-2"></i>Import
            </button>
        </div>
    </div>

    <!-- Settings Form -->
    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @foreach($categories as $categoryKey => $categoryName)
            @if(isset($settings[$categoryKey]))
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-{{ $categoryKey === 'general' ? 'cogs' : ($categoryKey === 'security' ? 'shield-alt' : ($categoryKey === 'email' ? 'envelope' : ($categoryKey === 'ui' ? 'palette' : 'cog'))) }} me-2"></i>
                            {{ $categoryName }}
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            @foreach($settings[$categoryKey] as $setting)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">{{ ucwords(str_replace('_', ' ', $setting->key)) }}</label>

                                    @if($setting->type === 'boolean')
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   name="settings[{{ $setting->category }}.{{ $setting->key }}]"
                                                   value="true"
                                                   id="setting_{{ $setting->category }}_{{ $setting->key }}"
                                                   {{ $setting->getCastedValue() ? 'checked' : '' }}>
                                            <label class="form-check-label" for="setting_{{ $setting->category }}_{{ $setting->key }}">
                                                Enable
                                            </label>
                                        </div>
                                    @elseif($setting->type === 'integer')
                                        <input type="number"
                                               name="settings[{{ $setting->category }}.{{ $setting->key }}]"
                                               value="{{ $setting->getCastedValue() }}"
                                               class="form-control"
                                               placeholder="Enter {{ strtolower(str_replace('_', ' ', $setting->key)) }}">
                                    @elseif($setting->type === 'json' || $setting->type === 'array')
                                        <textarea name="settings[{{ $setting->category }}.{{ $setting->key }}]"
                                                  class="form-control"
                                                  rows="3"
                                                  placeholder="Enter JSON data">{{ is_array($setting->getCastedValue()) ? json_encode($setting->getCastedValue(), JSON_PRETTY_PRINT) : $setting->getCastedValue() }}</textarea>
                                    @else
                                        <input type="text"
                                               name="settings[{{ $setting->category }}.{{ $setting->key }}]"
                                               value="{{ $setting->getCastedValue() }}"
                                               class="form-control"
                                               placeholder="Enter {{ strtolower(str_replace('_', ' ', $setting->key)) }}">
                                    @endif

                                    @if($setting->description)
                                        <div class="form-text">{{ $setting->description }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <!-- Logo Upload -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-image me-2"></i>Logo & Branding
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Current Logo -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Current Logo</h6>
                        @php $logoPath = setting('ui.logo_path'); @endphp
                        @if($logoPath && Storage::disk('public')->exists($logoPath))
                            <img src="{{ storage_public_url($logoPath) }}"
                                 class="img-thumbnail"
                                 style="max-height: 100px; max-width: 200px;"
                                 alt="Current Logo">
                        @else
                            <div class="bg-primary rounded d-flex align-items-center justify-content-center text-white"
                                 style="width: 100px; height: 100px;">
                                <i class="fas fa-image fa-2x"></i>
                            </div>
                            <p class="text-muted mt-2">No logo uploaded</p>
                        @endif
                    </div>

                    <!-- Upload New Logo -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Upload New Logo</h6>
                        <input type="file"
                               name="logo"
                               accept="image/*"
                               class="form-control"
                               id="logoUpload">
                        <div class="form-text">
                            Supported formats: JPG, PNG, SVG. Max size: 2MB.
                        </div>

                        <!-- Preview -->
                        <div id="logoPreview" class="mt-3" style="display: none;">
                            <img id="previewImage" class="img-thumbnail" style="max-height: 100px; max-width: 200px;" alt="Preview">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex justify-content-end gap-2 mb-4">
            <button type="button" class="btn btn-outline-danger" onclick="resetSettings()">
                <i class="fas fa-undo me-2"></i>Reset to Defaults
            </button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Save Settings
            </button>
        </div>
    </form>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('settings.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Settings File</label>
                        <input type="file" name="settings_file" accept=".json" class="form-control" required>
                        <div class="form-text">Upload a JSON file with settings configuration</div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Warning:</strong> This will overwrite existing settings. Make sure to export current settings first.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Import Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Logo preview functionality
    document.getElementById('logoUpload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImage').src = e.target.result;
                document.getElementById('logoPreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            document.getElementById('logoPreview').style.display = 'none';
        }
    });

    // Show import modal
    function showImportModal() {
        new bootstrap.Modal(document.getElementById('importModal')).show();
    }

    // Reset settings
    function resetSettings() {
        if (confirm('Are you sure you want to reset all settings to their default values? This action cannot be undone.')) {
            fetch('{{ route('settings.reset') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error resetting settings. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error resetting settings. Please try again.');
            });
        }
    }

    // Auto-save indication
    document.querySelector('form').addEventListener('submit', function() {
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
        submitBtn.disabled = true;

        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 2000);
    });
</script>
@endpush
