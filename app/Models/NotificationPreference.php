<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_new_follower',
        'email_artwork_liked',
        'email_artwork_commented',
        'email_post_liked',
        'email_post_commented',
        'email_wall_added_nearby',
        'email_mentioned',
        'email_weekly_digest',
        'email_marketing',
        'push_new_follower',
        'push_artwork_liked',
        'push_artwork_commented',
        'push_post_liked',
        'push_post_commented',
        'push_wall_added_nearby',
        'push_mentioned',
        'push_live_events',
        'app_new_follower',
        'app_artwork_liked',
        'app_artwork_commented',
        'app_post_liked',
        'app_post_commented',
        'app_wall_added_nearby',
        'app_mentioned',
        'email_frequency',
        'push_frequency',
        'quiet_hours_start',
        'quiet_hours_end',
        'timezone',
    ];

    protected $casts = [
        'email_new_follower' => 'boolean',
        'email_artwork_liked' => 'boolean',
        'email_artwork_commented' => 'boolean',
        'email_post_liked' => 'boolean',
        'email_post_commented' => 'boolean',
        'email_wall_added_nearby' => 'boolean',
        'email_mentioned' => 'boolean',
        'email_weekly_digest' => 'boolean',
        'email_marketing' => 'boolean',
        'push_new_follower' => 'boolean',
        'push_artwork_liked' => 'boolean',
        'push_artwork_commented' => 'boolean',
        'push_post_liked' => 'boolean',
        'push_post_commented' => 'boolean',
        'push_wall_added_nearby' => 'boolean',
        'push_mentioned' => 'boolean',
        'push_live_events' => 'boolean',
        'app_new_follower' => 'boolean',
        'app_artwork_liked' => 'boolean',
        'app_artwork_commented' => 'boolean',
        'app_post_liked' => 'boolean',
        'app_post_commented' => 'boolean',
        'app_wall_added_nearby' => 'boolean',
        'app_mentioned' => 'boolean',
        'quiet_hours_start' => 'datetime:H:i',
        'quiet_hours_end' => 'datetime:H:i',
    ];

    const FREQUENCY_IMMEDIATE = 'immediate';
    const FREQUENCY_HOURLY = 'hourly';
    const FREQUENCY_DAILY = 'daily';
    const FREQUENCY_WEEKLY = 'weekly';
    const FREQUENCY_NEVER = 'never';

    /**
     * Get the user that owns the preferences
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if email notifications are enabled for a specific type
     */
    public function emailEnabled(string $type): bool
    {
        $field = "email_{$type}";
        return $this->$field ?? false;
    }

    /**
     * Check if push notifications are enabled for a specific type
     */
    public function pushEnabled(string $type): bool
    {
        $field = "push_{$type}";
        return $this->$field ?? false;
    }

    /**
     * Check if in-app notifications are enabled for a specific type
     */
    public function appEnabled(string $type): bool
    {
        $field = "app_{$type}";
        return $this->$field ?? false;
    }

    /**
     * Check if currently in quiet hours
     */
    public function inQuietHours(): bool
    {
        if (!$this->quiet_hours_start || !$this->quiet_hours_end) {
            return false;
        }

        $now = now($this->timezone ?? 'UTC');
        $start = $this->quiet_hours_start;
        $end = $this->quiet_hours_end;

        if ($start->lt($end)) {
            // Same day quiet hours (e.g., 22:00 to 08:00 next day)
            return $now->between($start, $end);
        } else {
            // Overnight quiet hours (e.g., 22:00 to 08:00 next day)
            return $now->gte($start) || $now->lte($end);
        }
    }

    /**
     * Get default preferences for a new user
     */
    public static function getDefaults(): array
    {
        return [
            'email_new_follower' => true,
            'email_artwork_liked' => true,
            'email_artwork_commented' => true,
            'email_post_liked' => true,
            'email_post_commented' => true,
            'email_wall_added_nearby' => true,
            'email_mentioned' => true,
            'email_weekly_digest' => true,
            'email_marketing' => false,
            'push_new_follower' => true,
            'push_artwork_liked' => true,
            'push_artwork_commented' => true,
            'push_post_liked' => true,
            'push_post_commented' => true,
            'push_wall_added_nearby' => true,
            'push_mentioned' => true,
            'push_live_events' => true,
            'app_new_follower' => true,
            'app_artwork_liked' => true,
            'app_artwork_commented' => true,
            'app_post_liked' => true,
            'app_post_commented' => true,
            'app_wall_added_nearby' => true,
            'app_mentioned' => true,
            'email_frequency' => self::FREQUENCY_IMMEDIATE,
            'push_frequency' => self::FREQUENCY_IMMEDIATE,
            'timezone' => 'UTC',
        ];
    }
}
