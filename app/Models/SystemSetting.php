<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    use HasFactory;

    protected static $cachedSettings = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category',
        'key',
        'value',
        'type',
        'description',
        'is_public',
        'is_encrypted',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'is_encrypted' => 'boolean',
    ];

    /**
     * Setting type constants
     */
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';
    const TYPE_ARRAY = 'array';

    /**
     * Setting category constants
     */
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_EMAIL = 'email';
    const CATEGORY_SECURITY = 'security';
    const CATEGORY_WORK_UPDATES = 'work_updates';
    const CATEGORY_UI = 'ui';
    const CATEGORY_NOTIFICATIONS = 'notifications';

    /**
     * Get all available setting types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_STRING => 'String',
            self::TYPE_INTEGER => 'Integer',
            self::TYPE_BOOLEAN => 'Boolean',
            self::TYPE_JSON => 'JSON',
            self::TYPE_ARRAY => 'Array',
        ];
    }

    /**
     * Get all available categories.
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_GENERAL => 'General Settings',
            self::CATEGORY_EMAIL => 'Email Configuration',
            self::CATEGORY_SECURITY => 'Security Settings',
            self::CATEGORY_WORK_UPDATES => 'Work Update Settings',
            self::CATEGORY_UI => 'User Interface',
            self::CATEGORY_NOTIFICATIONS => 'Notifications',
        ];
    }

    /**
     * Get default system settings.
     */
    public static function getDefaultSettings(): array
    {
        return [
            // General Settings
            'general.app_name' => [
                'value' => 'W Automation',
                'type' => self::TYPE_STRING,
                'description' => 'Application name displayed in the interface',
                'is_public' => true,
            ],
            'general.timezone' => [
                'value' => 'UTC',
                'type' => self::TYPE_STRING,
                'description' => 'Default timezone for the application',
                'is_public' => true,
            ],
            'general.date_format' => [
                'value' => 'Y-m-d',
                'type' => self::TYPE_STRING,
                'description' => 'Default date format',
                'is_public' => true,
            ],
            'general.records_per_page' => [
                'value' => '20',
                'type' => self::TYPE_INTEGER,
                'description' => 'Default number of records per page',
                'is_public' => true,
            ],

            // Work Update Settings
            'work_updates.approval_required' => [
                'value' => 'true',
                'type' => self::TYPE_BOOLEAN,
                'description' => 'Require approval for work updates',
                'is_public' => false,
            ],
            'work_updates.max_daily_submissions' => [
                'value' => '10',
                'type' => self::TYPE_INTEGER,
                'description' => 'Maximum daily work update submissions per agent',
                'is_public' => false,
            ],
            'work_updates.auto_approve_trusted_agents' => [
                'value' => 'false',
                'type' => self::TYPE_BOOLEAN,
                'description' => 'Auto-approve updates from trusted agents',
                'is_public' => false,
            ],
            'work_updates.required_fields' => [
                'value' => '["job_title","company","applied_date"]',
                'type' => self::TYPE_JSON,
                'description' => 'Required fields for work updates',
                'is_public' => false,
            ],

            // Email Settings
            'email.smtp_host' => [
                'value' => '',
                'type' => self::TYPE_STRING,
                'description' => 'SMTP server hostname',
                'is_public' => false,
                'is_encrypted' => true,
            ],
            'email.smtp_port' => [
                'value' => '587',
                'type' => self::TYPE_INTEGER,
                'description' => 'SMTP server port',
                'is_public' => false,
            ],
            'email.notification_enabled' => [
                'value' => 'true',
                'type' => self::TYPE_BOOLEAN,
                'description' => 'Enable email notifications',
                'is_public' => false,
            ],

            // Security Settings
            'security.two_factor_enabled' => [
                'value' => 'false',
                'type' => self::TYPE_BOOLEAN,
                'description' => 'Enable two-factor authentication',
                'is_public' => false,
            ],
            'security.session_lifetime' => [
                'value' => '120',
                'type' => self::TYPE_INTEGER,
                'description' => 'Session lifetime in minutes',
                'is_public' => false,
            ],
            'security.password_min_length' => [
                'value' => '8',
                'type' => self::TYPE_INTEGER,
                'description' => 'Minimum password length',
                'is_public' => false,
            ],

            // UI Settings
            'ui.theme' => [
                'value' => 'light',
                'type' => self::TYPE_STRING,
                'description' => 'Default UI theme',
                'is_public' => true,
            ],
            'ui.sidebar_collapsed' => [
                'value' => 'false',
                'type' => self::TYPE_BOOLEAN,
                'description' => 'Default sidebar state',
                'is_public' => true,
            ],
            'ui.enable_dark_mode' => [
                'value' => 'true',
                'type' => self::TYPE_BOOLEAN,
                'description' => 'Enable dark mode option',
                'is_public' => true,
            ],
            'ui.logo_path' => [
                'value' => '',
                'type' => self::TYPE_STRING,
                'description' => 'Path to the application logo',
                'is_public' => true,
            ],
        ];
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter public settings.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to search settings.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('key', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('category', 'like', "%{$search}%");
        });
    }

    /**
     * Get setting value with proper casting.
     */
    public function getCastedValue()
    {
        $value = $this->is_encrypted ? Crypt::decryptString($this->value) : $this->value;

        return match($this->type) {
            self::TYPE_BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_INTEGER => (int) $value,
            self::TYPE_JSON => json_decode($value, true),
            self::TYPE_ARRAY => json_decode($value, true) ?: [],
            default => $value,
        };
    }

    /**
     * Set setting value with proper casting and encryption.
     */
    public function setCastedValue($value): void
    {
        $processedValue = match($this->type) {
            self::TYPE_BOOLEAN => $value ? 'true' : 'false',
            self::TYPE_JSON, self::TYPE_ARRAY => json_encode($value),
            default => (string) $value,
        };

        $this->value = $this->is_encrypted ? Crypt::encryptString($processedValue) : $processedValue;
    }

    /**
     * Get setting by key with dot notation.
     */
    public static function get(string $key, $default = null)
    {
        $setting = static::getCachedSettings()->get($key);

        return $setting ? $setting->getCastedValue() : $default;
    }

    /**
     * Set setting by key with dot notation.
     */
    public static function set(string $key, $value, ?string $type = null): self
    {
        [$category, $settingKey] = explode('.', $key, 2);

        $setting = static::firstOrNew([
            'category' => $category,
            'key' => $settingKey,
        ]);

        if ($type) {
            $setting->type = $type;
        }

        $setting->setCastedValue($value);
        $setting->save();
        Cache::forget('system_settings');

        return $setting;
    }

    /**
     * Get all settings grouped by category.
     */
    public static function getAllGrouped(): array
    {
        return static::getCachedSettings()
                    ->sortBy([
                        ['category', 'asc'],
                        ['key', 'asc'],
                    ])
                    ->groupBy('category')
                    ->map(function ($settings) {
                        return $settings->mapWithKeys(function ($setting) {
                            return [$setting->key => $setting->getCastedValue()];
                        });
                    })
                    ->toArray();
    }

    /**
     * Get public settings for frontend.
     */
    public static function getPublicSettings(): array
    {
        return static::getCachedSettings()
                    ->filter(fn ($setting) => $setting->is_public)
                    ->mapWithKeys(function ($setting) {
                        return ["{$setting->category}.{$setting->key}" => $setting->getCastedValue()];
                    })
                    ->toArray();
    }

    /**
     * Get all system settings from cache.
     */
    protected static function getCachedSettings()
    {
        if (static::$cachedSettings !== null) {
            return static::$cachedSettings;
        }

        return static::$cachedSettings = Cache::remember('system_settings', 3600, function () {
            return static::query()
                ->orderBy('category')
                ->orderBy('key')
                ->get()
                ->keyBy(fn ($setting) => "{$setting->category}.{$setting->key}");
        });
    }

    /**
     * Create default system settings.
     */
    public static function createDefaults(): void
    {
        foreach (self::getDefaultSettings() as $fullKey => $data) {
            [$category, $key] = explode('.', $fullKey, 2);

            self::firstOrCreate([
                'category' => $category,
                'key' => $key,
            ], array_merge($data, [
                'category' => $category,
                'key' => $key,
            ]));
        }
    }

    /**
     * Get category display name.
     */
    public function getCategoryDisplayNameAttribute(): string
    {
        $categories = self::getCategories();
        return $categories[$this->category] ?? ucwords(str_replace('_', ' ', $this->category));
    }

    /**
     * Get full key (category.key).
     */
    public function getFullKeyAttribute(): string
    {
        return "{$this->category}.{$this->key}";
    }
}
