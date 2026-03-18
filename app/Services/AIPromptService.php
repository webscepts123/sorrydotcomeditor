<?php

namespace App\Services;

class AIPromptService
{
    public function buildPrompt($scene)
    {
        // Logic for Seedance 2.0 goes here
        return "Cinematic Noir: " . $scene->script_segment;
    }
}