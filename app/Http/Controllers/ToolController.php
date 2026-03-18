<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function syncFace()
    {
        return view('tools.sync-face');
    }

    public function genScore()
    {
        return view('tools.gen-score');
    }

    public function script()
    {
        return view('tools.script');
    }
}
