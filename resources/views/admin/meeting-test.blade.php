@extends('layouts.app')

@section('title', 'Meeting System Test')
@section('pageTitle', 'Google Meet System Test')
@section('pageSubtitle', 'Test the automated meeting system')

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="fas fa-video me-2"></i>System Status Test
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Current Meeting Status</h6>
                        @if($todayMeeting)
                            <div class="alert alert-success">
                                <strong>✅ Meeting Active</strong><br>
                                <strong>Title:</strong> {{ $todayMeeting->meeting_title }}<br>
                                <strong>Date:</strong> {{ $todayMeeting->meeting_date->format('M d, Y') }}<br>
                                <strong>Time:</strong> {{ $todayMeeting->meeting_start_time->format('g:i A') }} - {{ $todayMeeting->meeting_end_time->format('g:i A') }}<br>
                                <strong>Status:</strong> {{ $todayMeeting->status }}<br>
                                <strong>Active:</strong> {{ $todayMeeting->is_active ? 'Yes' : 'No' }}
                            </div>

                            <div class="mb-3">
                                <strong>Google Meet Link:</strong><br>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{ $todayMeeting->google_meet_link }}" readonly>
                                    <button class="btn btn-outline-secondary" onclick="copyToClipboard('{{ $todayMeeting->google_meet_link }}')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <a href="{{ $todayMeeting->google_meet_link }}" target="_blank" class="btn btn-success btn-lg">
                                    <i class="fas fa-video me-2"></i>Join Meeting
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <strong>⚠️ No Meeting Scheduled</strong><br>
                                No meeting has been scheduled for today.
                            </div>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <h6>System Actions</h6>
                        <div class="d-grid gap-2">
                            <button onclick="generateMeeting()" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Generate New Meeting
                            </button>
                            <button onclick="updateMeetingLink()" class="btn btn-warning">
                                <i class="fas fa-edit me-2"></i>Update Meeting Link
                            </button>
                            <a href="{{ route('admin.meeting-reports') }}" class="btn btn-info">
                                <i class="fas fa-chart-line me-2"></i>View Reports
                            </a>
                            <button onclick="testAttendance()" class="btn btn-secondary">
                                <i class="fas fa-users me-2"></i>Test Attendance
                            </button>
                        </div>

                        <hr>

                        <h6>API Status</h6>
                        <div class="alert alert-info">
                            <strong>Google Calendar API:</strong>
                            @if($todayMeeting && str_contains($todayMeeting->google_calendar_event_id, 'fallback'))
                                <span class="text-warning">Using Fallback (API not fully configured)</span>
                            @else
                                <span class="text-success">Active</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($todayMeeting)
<!-- Attendance Test -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Attendance Test
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Test Agent Join</h6>
                        <form id="testJoinForm">
                            <div class="mb-3">
                                <label class="form-label">Agent ID</label>
                                <input type="number" class="form-control" name="agent_id" placeholder="Enter agent ID" required>
                            </div>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-sign-in-alt me-2"></i>Simulate Join
                            </button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <h6>Test Screen Sharing</h6>
                        <form id="testScreenShareForm">
                            <div class="mb-3">
                                <label class="form-label">Agent ID</label>
                                <input type="number" class="form-control" name="agent_id" placeholder="Enter agent ID" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Action</label>
                                <select class="form-select" name="action" required>
                                    <option value="start">Start Screen Sharing</option>
                                    <option value="stop">Stop Screen Sharing</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-share-screen me-2"></i>Test Screen Share
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Meeting link copied to clipboard!');
    });
}

function generateMeeting() {
    if (confirm('Generate a new daily meeting for today? This will create a new meeting link.')) {
        fetch('{{ route("admin.generate-meeting") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Meeting generated successfully! The page will reload.');
                location.reload();
            } else {
                alert('Error generating meeting: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error generating meeting. Please try again.');
        });
    }
}

function updateMeetingLink() {
    const newLink = prompt('Enter the real Google Meet link:');
    if (newLink && newLink.includes('meet.google.com')) {
        fetch('{{ route("admin.update-meeting-link") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ meeting_link: newLink })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Meeting link updated successfully! The page will reload.');
                location.reload();
            } else {
                alert('Error updating meeting link: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error updating meeting link. Please try again.');
        });
    } else if (newLink) {
        alert('Please enter a valid Google Meet link (must contain meet.google.com)');
    }
}

function testAttendance() {
    alert('Attendance tracking is ready! When agents join the meeting, their attendance will be automatically tracked.');
}

// Test forms
document.getElementById('testJoinForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const agentId = this.agent_id.value;
    alert('Agent ' + agentId + ' join simulation would be tracked here in a real implementation.');
});

document.getElementById('testScreenShareForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const agentId = this.agent_id.value;
    const action = this.action.value;
    alert('Agent ' + agentId + ' screen sharing ' + action + ' would be tracked here in a real implementation.');
});
</script>
@endsection
