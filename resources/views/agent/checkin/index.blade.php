@extends('layouts.app')

@section('title', 'Check In/Out')
@section('pageTitle', 'Check In/Out')
@section('pageSubtitle', 'Track your work hours and attendance')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Check In/Out</h2>
                <p class="text-muted mb-0">Track your work hours and daily attendance</p>
            </div>
        </div>

        <!-- Check In/Out Status Card -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clock me-2"></i>Current Status
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        @if($isCheckedIn)
                            <div class="mb-3">
                                <i class="fas fa-check-circle fa-3x text-success"></i>
                            </div>
                            <h4 class="text-success mb-2">Checked In</h4>
                            <p class="text-muted">You are currently checked in</p>
                            <button id="checkOutBtn" class="btn btn-black btn-lg">
                                <i class="fas fa-sign-out-alt me-2"></i>Check Out
                            </button>
                        @else
                            <div class="mb-3">
                                <i class="fas fa-clock fa-3x text-warning"></i>
                            </div>
                            <h4 class="text-warning mb-2">Not Checked In</h4>
                            <p class="text-muted">Click below to check in</p>
                            <button id="checkInBtn" class="btn btn-black btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Check In
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Today's Work Hours
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-12">
                                <div class="stats-number text-primary">{{ number_format($workHours['total_hours'], 1) }}h</div>
                                <div class="stats-label">Total Hours Worked Today</div>
                            </div>
                        </div>

                        @if($workHours['total_hours'] >= 7)
                            <div class="text-center py-3">
                                <small class="text-success">
                                    <i class="fas fa-check-circle me-1"></i>
                                    Great work! You've been productive today.
                                </small>
                            </div>
                        @elseif($workHours['total_hours'] > 0)
                            <div class="text-center py-3">
                                <small class="text-info">
                                    <i class="fas fa-clock me-1"></i>
                                    Keep up the good work! {{ number_format(7 - $workHours['total_hours'], 1) }}h more to reach your daily goal.
                                </small>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <small class="text-muted">No work hours recorded today</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Activities -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Today's Activities
                </h5>
            </div>
            <div class="card-body">
                @if($todayActivities->count() > 0)
                    <div class="timeline">
                        @foreach($todayActivities as $activity)
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <div class="bg-{{ $activity->activity_type === 'check_in' ? 'success' : ($activity->activity_type === 'check_out' ? 'danger' : 'info') }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <i class="fas fa-{{ $activity->activity_type === 'check_in' ? 'sign-in-alt' : ($activity->activity_type === 'check_out' ? 'sign-out-alt' : 'clock') }} fa-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="small text-muted">{{ $activity->activity_time->format('H:i') }}</div>
                                        <div class="fw-bold">
                                            @if($activity->activity_type === 'check_in')
                                                Checked In
                                            @elseif($activity->activity_type === 'check_out')
                                                Checked Out
                                            @else
                                                {{ ucfirst(str_replace('_', ' ', $activity->activity_type)) }}
                                            @endif
                                        </div>
                                        @if($activity->additional_data && isset($activity->additional_data['notes']))
                                            <div class="small text-muted">{{ $activity->additional_data['notes'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No activities recorded today</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Check In Modal -->
<div id="checkInModal" class="custom-modal" style="display: none;">
    <div class="custom-modal-overlay" onclick="closeCheckInModal()"></div>
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">Check In</h5>
            <button type="button" class="custom-modal-close" onclick="closeCheckInModal()">&times;</button>
        </div>
        <form id="checkInForm">
            <div class="custom-modal-body">
                <div class="mb-3">
                    <label for="checkInLocation" class="form-label">Location (Optional)</label>
                    <input type="text" class="form-control" id="checkInLocation" name="location" placeholder="e.g., Office, Home">
                </div>
                <div class="mb-3">
                    <label for="checkInNotes" class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" id="checkInNotes" name="notes" rows="3" placeholder="Any notes about your work session..."></textarea>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-white" onclick="closeCheckInModal()">Cancel</button>
                <button type="submit" class="btn btn-black">Check In</button>
            </div>
        </form>
    </div>
</div>

<!-- Check Out Modal -->
<div id="checkOutModal" class="custom-modal" style="display: none;">
    <div class="custom-modal-overlay" onclick="closeCheckOutModal()"></div>
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <h5 class="custom-modal-title">Check Out</h5>
            <button type="button" class="custom-modal-close" onclick="closeCheckOutModal()">&times;</button>
        </div>
        <form id="checkOutForm">
            <div class="custom-modal-body">
                <div class="mb-3">
                    <label for="checkOutLocation" class="form-label">Location (Optional)</label>
                    <input type="text" class="form-control" id="checkOutLocation" name="location" placeholder="e.g., Office, Home">
                </div>
                <div class="mb-3">
                    <label for="checkOutNotes" class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" id="checkOutNotes" name="notes" rows="3" placeholder="Summary of work completed..."></textarea>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-white" onclick="closeCheckOutModal()">Cancel</button>
                <button type="submit" class="btn btn-black">Check Out</button>
            </div>
        </form>
    </div>
</div>

<style>
.timeline-item {
    position: relative;
}
.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 17px;
    top: 45px;
    height: calc(100% - 10px);
    width: 2px;
    background: #e5e7eb;
}

/* Fix modal overlay issues */
.modal {
    z-index: 1055 !important;
}

.modal-backdrop {
    z-index: 1050 !important;
}

.modal-dialog {
    z-index: 1056 !important;
}

/* Ensure modal content is clickable */
.modal-content {
    position: relative;
    z-index: 1057 !important;
}

/* Fix any potential overlay issues */
.modal.show {
    display: block !important;
}

/* Ensure modal backdrop doesn't interfere */
.modal-backdrop.show {
    opacity: 0.5;
}

/* Custom Modal Styles */
.custom-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.custom-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1;
}

.custom-modal-content {
    position: relative;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    z-index: 2;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.custom-modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.custom-modal-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
}

.custom-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #6b7280;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.custom-modal-close:hover {
    color: #374151;
}

.custom-modal-body {
    padding: 1.5rem;
}

.custom-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
}
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Check In functionality
    $('#checkInBtn').click(function(e) {
        e.preventDefault();
        console.log('Check in button clicked');

        // Show custom modal
        $('#checkInModal').show();
    });

    $('#checkInForm').submit(function(e) {
        e.preventDefault();

        // Disable submit button to prevent double submission
        $(this).find('button[type="submit"]').prop('disabled', true);

        $.ajax({
            url: '{{ route("agent.checkin.check-in") }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#checkInModal').hide();
                    location.reload();
                } else {
                    alert(response.message || 'Check-in failed. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                // Re-enable submit button
                $('#checkInForm').find('button[type="submit"]').prop('disabled', false);
            }
        });
    });

    // Check Out functionality
    $('#checkOutBtn').click(function(e) {
        e.preventDefault();
        console.log('Check out button clicked');

        // Show custom modal
        $('#checkOutModal').show();
    });

    $('#checkOutForm').submit(function(e) {
        e.preventDefault();
        console.log('Check out form submitted');

        // Disable submit button to prevent double submission
        $(this).find('button[type="submit"]').prop('disabled', true);

        $.ajax({
            url: '{{ route("agent.checkin.check-out") }}',
            method: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#checkOutModal').hide();
                    location.reload();
                } else {
                    alert(response.message || 'Check-out failed. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Check-out AJAX Error:', xhr.responseText);
                console.error('Status:', status);
                console.error('Error:', error);
                alert('An error occurred during check-out. Please try again.');
            },
            complete: function() {
                // Re-enable submit button
                $('#checkOutForm').find('button[type="submit"]').prop('disabled', false);
            }
        });
    });

    // Handle modal events
    $('#checkInModal').on('shown.bs.modal', function () {
        console.log('Check-in modal shown');
    });

    $('#checkOutModal').on('shown.bs.modal', function () {
        console.log('Check-out modal shown');
    });

    $('#checkInModal').on('hidden.bs.modal', function () {
        console.log('Check-in modal hidden');
    });

    $('#checkOutModal').on('hidden.bs.modal', function () {
        console.log('Check-out modal hidden');
    });
});

// Custom modal functions
function closeCheckInModal() {
    $('#checkInModal').hide();
}

function closeCheckOutModal() {
    $('#checkOutModal').hide();
}

function showCheckInModal() {
    $('#checkInModal').show();
}

function showCheckOutModal() {
    $('#checkOutModal').show();
}
</script>
@endpush
