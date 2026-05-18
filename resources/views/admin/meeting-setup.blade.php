<x-app-layout>
    <x-slot name="title">Meeting Setup</x-slot>
    <x-slot name="pageTitle">Meeting Setup</x-slot>
    <x-slot name="pageSubtitle">Add meeting links manually for any date with start and end times.</x-slot>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus me-2"></i>Add / Update Meeting
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form method="POST" action="{{ route('admin.meeting-setup.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Date</label>
                            <input type="date" name="date" class="form-control" value="{{ old('date') ?? date('Y-m-d') }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">Start Time</label>
                                <input type="time" name="start_time" class="form-control" value="{{ old('start_time') ?? '09:00' }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold">End Time</label>
                                <input type="time" name="end_time" class="form-control" value="{{ old('end_time') ?? '10:00' }}" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Meeting Link</label>
                            <input type="url" name="meet_link" class="form-control" placeholder="https://meet.example.com/room" value="{{ old('meet_link') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Title (optional)</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Team meeting">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description (optional)</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="What this meeting is for">{{ old('description') }}</textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-black">
                                <i class="fas fa-save me-2"></i>Save Meeting
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><i class="fas fa-calendar-alt me-2"></i>Upcoming & Recent</h5>
                </div>
                <div class="card-body">
                    @if($upcomingMeetings->count())
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Link</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingMeetings as $meeting)
                                        @php
                                            $isLive = $meeting->isActive();
                                            $hasEnded = $meeting->end_time ? $meeting->end_time->lt(now()) : $meeting->date->isPast();
                                            $statusLabel = !$meeting->is_active
                                                ? 'Inactive'
                                                : ($isLive ? 'Active' : ($hasEnded ? 'Ended' : 'Upcoming'));
                                            $statusClass = !$meeting->is_active
                                                ? 'bg-secondary'
                                                : ($isLive ? 'bg-success' : ($hasEnded ? 'bg-dark' : 'bg-info'));
                                        @endphp
                                        <tr>
                                            <td>{{ $meeting->date->format('M d, Y') }}</td>
                                            <td>{{ $meeting->start_time?->format('g:i A') ?? '-' }} - {{ $meeting->end_time?->format('g:i A') ?? '-' }}</td>
                                            <td class="text-truncate" style="max-width: 240px;">
                                                <a href="{{ $meeting->meet_link }}" target="_blank">{{ $meeting->meet_link }}</a>
                                            </td>
                                            <td>
                                                <span class="badge {{ $statusClass }}">
                                                    {{ $statusLabel }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    <a href="{{ $meeting->meet_link }}" target="_blank" class="btn btn-sm btn-border-black" title="Open Meeting">
                                                        <i class="fas fa-arrow-up-right-from-square"></i>
                                                    </a>
                                                    <form method="POST" action="{{ route('admin.meeting-setup.destroy', $meeting) }}" class="d-inline" onsubmit="return confirm('Delete the meeting scheduled for {{ $meeting->date->format('M d, Y') }}? This will also remove related attendance records.');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-delete" title="Delete Meeting">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No meetings scheduled yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
