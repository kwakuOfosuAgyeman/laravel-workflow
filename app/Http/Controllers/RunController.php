<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Run;

class RunController extends Controller
{
    public function show(Run $run)
    {
        // Eager load relationships for better performance
        $run->load([
            'workflow',
            'logs.step',
        ]);

        return view('runs.show', compact('run'));
    }
}
