<?php

namespace App\Models;

use App\Models\Concerns\HasSlugRouteKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Meeting extends Model
{
    use HasFactory, HasSlugRouteKey;

    protected $fillable = [
        'date',
        'meet_link',
        'google_event_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'is_active'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the attendances for this meeting
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get agents who attended this meeting
     */
    public function agents()
    {
        return $this->belongsToMany(User::class, 'attendances', 'meeting_id', 'agent_id')
                    ->withPivot('join_time', 'leave_time', 'duration_minutes', 'status')
                    ->withTimestamps();
    }

    /**
     * Check if meeting is active (within time range)
     */
    public function isActive()
    {
        $now = Carbon::now();
        return $this->is_active &&
               $now->between($this->start_time, $this->end_time);
    }

    /**
     * Get meeting duration in hours
     */
    public function getDurationInHours()
    {
        return rounded_time_value($this->start_time->diffInHours($this->end_time));
    }

    /**
     * Get total attendance count
     */
    public function getAttendanceCount()
    {
        return $this->attendances()->count();
    }

    /**
     * Get active attendance count (currently in meeting)
     */
    public function getActiveAttendanceCount()
    {
        return $this->attendances()->where('status', 'joined')->count();
    }

    /**
     * Scope for today's meeting
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }

    /**
     * Scope for active meetings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected function routeKeyPrefix(): string
    {
        return 'm';
    }

    protected function routeKeySourceColumn(): ?string
    {
        return 'title';
    }
}
