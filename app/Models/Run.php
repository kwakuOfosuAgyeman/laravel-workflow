<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Run extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * The attributes that should be appended.
     */
    protected $appends = ['status_badge_class', 'formatted_duration'];

    /**
     * Run status constants.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_RUNNING = 'running';
    const STATUS_SUCCEEDED = 'succeeded';
    const STATUS_FAILED = 'failed';

    /**
     * Get the workflow for this run.
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the logs for this run.
     */
    public function logs()
    {
        return $this->hasMany(RunLog::class)->orderBy('created_at');
    }

    /**
     * Mark the run as started.
     */
    public function markAsStarted()
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark the run as succeeded.
     */
    public function markAsSucceeded()
    {
        $this->update([
            'status' => self::STATUS_SUCCEEDED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark the run as failed.
     */
    public function markAsFailed()
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Get the duration in seconds.
     */
    public function getDurationAttribute()
    {
        if ($this->started_at && $this->completed_at) {
            return $this->completed_at->diffInSeconds($this->started_at);
        }

        return null;
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDurationAttribute()
    {
        $duration = $this->duration;

        if ($duration === null) {
            return 'In progress';
        }

        if ($duration < 60) {
            return "{$duration}s";
        }

        $minutes = floor($duration / 60);
        $seconds = $duration % 60;
        return "{$minutes}m {$seconds}s";
    }

    /**
     * Check if the run is complete.
     */
    public function isComplete()
    {
        return in_array($this->status, [self::STATUS_SUCCEEDED, self::STATUS_FAILED]);
    }

    /**
     * Check if the run is in progress.
     */
    public function isInProgress()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_RUNNING]);
    }

    /**
     * Get the status badge class for UI.
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'bg-gray-100 text-gray-800',
            self::STATUS_RUNNING => 'bg-blue-100 text-blue-800',
            self::STATUS_SUCCEEDED => 'bg-green-100 text-green-800',
            self::STATUS_FAILED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
