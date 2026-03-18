<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SceneController;
use App\Http\Controllers\CharacterController;
use App\Http\Controllers\EditorController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SoundtrackController;

/*
|--------------------------------------------------------------------------
| Authentication & Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Production Hub (Void System)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // Core Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    /*
    |----------------------------------------------------------------------
    | Project-Centric Actions (The 2.5 Hour Movie Orchestration)
    |----------------------------------------------------------------------
    */
    Route::resource('projects', ProjectController::class);

    Route::prefix('projects/{project}')->group(function () {
        // High-Level Visual Interfaces
        Route::get('/editor', [ProjectController::class, 'editor'])->name('projects.editor');
        Route::get('/timeline', [ProjectController::class, 'timeline'])->name('projects.timeline');
        
        // Orchestration & Rendering Commands
        Route::post('/render-batch', [ProjectController::class, 'renderBatch'])->name('projects.render-batch');
        Route::get('/export-xml', [ProjectController::class, 'exportXml'])->name('projects.export-xml');
    });

    /*
    |----------------------------------------------------------------------
    | Scene & Asset Logic (The 15-Second Clips)
    |----------------------------------------------------------------------
    | Note: This single resource line handles create, store, edit, update, and destroy.
    */
    Route::resource('scenes', SceneController::class);
    
    // Specific Action: Trigger single AI generation via AJAX in the Editor
    Route::post('/scenes/{scene}/render', [SceneController::class, 'render'])->name('scenes.render');

    /*
    |----------------------------------------------------------------------
    | Staff & Cast Management (AI Seeds & VFX Editors)
    |----------------------------------------------------------------------
    */
    Route::resource('characters', CharacterController::class);
    Route::resource('editors', EditorController::class);

    /*
    |----------------------------------------------------------------------
    | Audio & Master Score
    |----------------------------------------------------------------------
    */
    Route::resource('soundtracks', SoundtrackController::class);

    /*
    |----------------------------------------------------------------------
    | System Configuration (Contabo & Seedance API Keys)
    |----------------------------------------------------------------------
    */
    Route::controller(SettingController::class)->group(function () {
        Route::get('/settings', 'index')->name('settings');
        Route::post('/settings/update', 'update')->name('settings.update');
        Route::post('/settings/api-refresh', 'refreshApiKey')->name('settings.api-refresh');
    });

});