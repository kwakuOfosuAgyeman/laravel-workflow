<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Step extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'type',
        'config',
        'step_order',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    /**
     * Available step types.
     */
    const TYPE_DELAY = 'delay';
    const TYPE_HTTP_CHECK = 'http_check';

    /**
     * Get the workflow that owns this step.
     */
    public function workflow()
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the run logs for this step.
     */
    public function runLogs()
    {
        return $this->hasMany(RunLog::class);
    }

    /**
     * Scope to get steps of a specific type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get all available step types.
     */
    public static function getAvailableTypes()
    {
        return [
            self::TYPE_DELAY => 'Delay',
            self::TYPE_HTTP_CHECK => 'HTTP Check',
        ];
    }

    /**
     * Validate step configuration based on type.
     */
    public function validateConfig()
    {
        $config = $this->config;

        return match($this->type) {
            self::TYPE_DELAY => isset($config['seconds']) && is_numeric($config['seconds']),
            self::TYPE_HTTP_CHECK => isset($config['url']) && filter_var($config['url'], FILTER_VALIDATE_URL),
            default => false,
        };
    }

    /**
     * Move this step up in order.
     */
    public function moveUp()
    {
        $previous = $this->workflow->steps()
            ->where('step_order', '<', $this->step_order)
            ->orderBy('step_order', 'desc')
            ->first();

        if ($previous) {
            $temp = $this->step_order;
            $this->step_order = $previous->step_order;
            $previous->step_order = $temp;

            $this->save();
            $previous->save();
        }
    }

    /**
     * Move this step down in order.
     */
    public function moveDown()
    {
        $next = $this->workflow->steps()
            ->where('step_order', '>', $this->step_order)
            ->orderBy('step_order', 'asc')
            ->first();

        if ($next) {
            $temp = $this->step_order;
            $this->step_order = $next->step_order;
            $next->step_order = $temp;

            $this->save();
            $next->save();
        }
    }
}
