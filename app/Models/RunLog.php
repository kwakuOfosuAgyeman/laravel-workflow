<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class RunLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'run_id',
        'step_id',
        'level',
        'message',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * Log level constants.
     */
    const LEVEL_INFO = 'info';
    const LEVEL_WARN = 'warn';
    const LEVEL_ERROR = 'error';

    /**
     * The attributes that should be appended.
     */
    protected $appends = ['level_badge_class'];

    /**
     * Get the run that owns this log.
     */
    public function run()
    {
        return $this->belongsTo(Run::class);
    }

    /**
     * Get the step that this log is for.
     */
    public function step()
    {
        return $this->belongsTo(Step::class);
    }

    /**
     * Create an info log entry.
     */
    public static function info(Run $run, ?Step $step, string $message)
    {
        return self::create([
            'run_id' => $run->id,
            'step_id' => $step?->id,
            'level' => self::LEVEL_INFO,
            'message' => $message,
            'timestamp' => now(),
        ]);
    }

    /**
     * Create a warning log entry.
     */
    public static function warn(Run $run, ?Step $step, string $message)
    {
        return self::create([
            'run_id' => $run->id,
            'step_id' => $step?->id,
            'level' => self::LEVEL_WARN,
            'message' => $message,
            'timestamp' => now(),
        ]);
    }

    /**
     * Create an error log entry.
     */
    public static function error(Run $run, ?Step $step, string $message)
    {
        return self::create([
            'run_id' => $run->id,
            'step_id' => $step?->id,
            'level' => self::LEVEL_ERROR,
            'message' => $message,
            'timestamp' => now(),
        ]);
    }

    /**
     * Get the CSS class for the level badge.
     */
    public function getLevelBadgeClassAttribute()
    {
        return match($this->level) {
            self::LEVEL_INFO => 'bg-blue-100 text-blue-800',
            self::LEVEL_WARN => 'bg-yellow-100 text-yellow-800',
            self::LEVEL_ERROR => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
