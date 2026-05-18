<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScreenSharingLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'agent_id',
        'started_at',
        'ended_at',
        'duration_minutes',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
