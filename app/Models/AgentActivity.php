<?php

namespace App\Models;

use App\Services\IpLocationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AgentActivity extends Model
{
    use HasFactory;

    protected ?array $resolvedLocation = null;

    protected $fillable = [
        'agent_id',
        'activity_type',
        'page_url',
        'page_title',
        'ip_address',
        'user_agent',
        'activity_time',
        'additional_data',
    ];

    protected $casts = [
        'activity_time' => 'datetime',
        'additional_data' => 'array',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function getLocationCityAttribute(): string
    {
        return $this->resolveLocation()['city'];
    }

    public function getLocationCountryAttribute(): string
    {
        return $this->resolveLocation()['country'];
    }

    public function getLocationLabelAttribute(): string
    {
        return $this->resolveLocation()['label'];
    }

    /**
     * Get today's activities for an agent
     */
    public static function getTodayActivities($agentId)
    {
        return self::where('agent_id', $agentId)
            ->whereDate('activity_time', today())
            ->orderBy('activity_time', 'desc')
            ->get();
    }

    /**
     * Get agent's work hours for today
     */
    public static function getTodayWorkHours($agentId, $date = null)
    {
        $date = $date ? Carbon::parse($date) : today();

        $checkIns = self::where('agent_id', $agentId)
            ->where('activity_type', 'check_in')
            ->whereDate('activity_time', $date)
            ->orderBy('activity_time')
            ->get();

        $checkOuts = self::where('agent_id', $agentId)
            ->where('activity_type', 'check_out')
            ->whereDate('activity_time', $date)
            ->orderBy('activity_time')
            ->get();

        $totalMinutes = 0;
        $sessions = [];

        foreach ($checkIns as $index => $checkIn) {
            $checkOut = $checkOuts->where('activity_time', '>', $checkIn->activity_time)->first();

            if ($checkOut) {
                $sessionMinutes = rounded_time_value($checkIn->activity_time->diffInMinutes($checkOut->activity_time));
                $totalMinutes += $sessionMinutes;
                $sessions[] = [
                    'check_in' => $checkIn->activity_time,
                    'check_out' => $checkOut->activity_time,
                    'duration' => $sessionMinutes,
                ];
            }
        }

        return [
            'total_minutes' => $totalMinutes,
            'total_hours' => round($totalMinutes / 60, 2),
            'sessions' => $sessions,
        ];
    }

    /**
     * Get agent's page visits for today
     */
    public static function getTodayPageVisits($agentId, $date = null)
    {
        $date = $date ? Carbon::parse($date) : today();

        return self::where('agent_id', $agentId)
            ->where('activity_type', 'page_visit')
            ->whereDate('activity_time', $date)
            ->select('page_url', 'page_title', 'activity_time')
            ->orderBy('activity_time', 'desc')
            ->get();
    }

    /**
     * Get daily report for admin
     */
    public static function getDailyReport($date = null)
    {
        $date = $date ? Carbon::parse($date) : today();

        $agents = User::whereHas('role', function($query) {
            $query->where('name', 'agent');
        })->get();

        $report = [
            'total_agents' => $agents->count(),
            'active_agents' => 0,
            'total_hours' => 0,
            'total_page_views' => 0,
            'agents' => [],
            'recent_activities' => [],
        ];

        $recentActivities = collect();

        foreach ($agents as $agent) {
            $activities = self::where('agent_id', $agent->id)
                ->whereDate('activity_time', $date)
                ->orderBy('activity_time')
                ->get();

            $pageVisits = $activities->where('activity_type', 'page_visit')->count();
            $checkIns = $activities->where('activity_type', 'check_in')->count();
            $workHours = self::getTodayWorkHours($agent->id, $date);
            $lastActivity = $activities->last()?->activity_time;
            $isActive = $activities->isNotEmpty();

            if ($isActive) {
                $report['active_agents']++;
            }

            $report['total_hours'] += $workHours['total_hours'];
            $report['total_page_views'] += $pageVisits;

            $report['agents'][] = [
                'id' => $agent->id,
                'route_key' => $agent->getRouteKey(),
                'name' => $agent->name,
                'email' => $agent->email,
                'is_active' => $isActive,
                'page_views' => $pageVisits,
                'page_visits' => $pageVisits,
                'work_hours' => $workHours['total_hours'],
                'sessions' => $workHours['sessions'],
                'check_ins' => $checkIns,
                'last_activity' => $lastActivity,
                'activities' => $activities,
            ];

            foreach ($activities->sortByDesc('activity_time')->take(5) as $activity) {
                $recentActivities->push([
                    'agent_name' => $agent->name,
                    'type' => $activity->activity_type,
                    'page' => $activity->page_title ?: $activity->page_url,
                    'time' => $activity->activity_time->format('h:i A'),
                    'activity_time' => $activity->activity_time,
                ]);
            }
        }

        $report['total_hours'] = round($report['total_hours'], 2);
        $report['recent_activities'] = $recentActivities
            ->sortByDesc('activity_time')
            ->take(12)
            ->map(function ($activity) {
                unset($activity['activity_time']);

                return $activity;
            })
            ->values()
            ->all();

        return $report;
    }

    protected function resolveLocation(): array
    {
        if ($this->resolvedLocation !== null) {
            return $this->resolvedLocation;
        }

        $additional = is_array($this->additional_data) ? $this->additional_data : [];
        $city = trim((string) ($additional['location_city'] ?? $additional['city'] ?? ''));
        $country = trim((string) ($additional['location_country'] ?? $additional['country'] ?? ''));

        if ($city !== '' || $country !== '') {
            if ($city === '') {
                $city = 'Unknown City';
            }

            if ($country === '') {
                $country = 'Unknown Country';
            }

            return $this->resolvedLocation = [
                'city' => $city,
                'country' => $country,
                'label' => $city . ', ' . $country,
            ];
        }

        return $this->resolvedLocation = app(IpLocationService::class)->resolve($this->ip_address);
    }
}
