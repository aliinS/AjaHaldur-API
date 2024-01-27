<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function time()
    {
        return response()->json([
            'time' => now()
        ]);
    }
}
