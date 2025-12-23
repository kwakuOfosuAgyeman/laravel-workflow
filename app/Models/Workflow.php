<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the steps for this workflow.
     */
    public function steps()
    {
        return $this->hasMany(Step::class)->orderBy('step_order');
    }

    /**
     * Get the runs for this workflow.
     */
    public function runs()
    {
        return $this->hasMany(Run::class)->latest();
    }

    /**
     * Get the most recent run.
     */
    public function latestRun()
    {
        return $this->hasOne(Run::class)->latestOfMany();
    }
}
