<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'meeting_id',
        'join_time',
        'leave_time',
        'duration_minutes',
        'status',
        'screen_shared',
        'screen_share_started_at',
        'screen_share_ended_at'
    ];

    protected $casts = [
        'join_time' => 'datetime',
        'leave_time' => 'datetime',
        'screen_shared' => 'boolean',
        'screen_share_started_at' => 'datetime',
        'screen_share_ended_at' => 'datetime',
    ];

    /**
     * Get the agent who attended
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the meeting
     */
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * Check if attendance is active (still in meeting)
     */
    public function isActive()
    {
        return $this->status === 'joined' && is_null($this->leave_time);
    }

    /**
     * Get duration in human readable format
     */
    public function getDurationFormatted()
    {
        if (!$this->duration_minutes) {
            return 'N/A';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Calculate duration when leaving
     */
    public function calculateDuration()
    {
        if ($this->leave_time) {
            $this->duration_minutes = rounded_time_value($this->join_time->diffInMinutes($this->leave_time));
            $this->save();
        }
    }

    /**
     * Mark as left
     */
    public function markAsLeft()
    {
        $this->leave_time = Carbon::now();
        $this->status = 'left';
        $this->calculateDuration();
    }

    /**
     * Scope for active attendances (still in meeting)
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'joined')->whereNull('leave_time');
    }

    /**
     * Scope for completed attendances
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'left')->whereNotNull('leave_time');
    }

    /**
     * Scope for today's attendances
     */
    public function scopeToday($query)
    {
        return $query->whereDate('join_time', Carbon::today());
    }

    /**
     * Get the screen sharing logs for this attendance
     */
    public function screenSharingLogs()
    {
        return $this->hasMany(ScreenSharingLog::class, 'attendance_id');
    }
}
