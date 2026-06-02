<?php

use App\Http\Controllers\Api\BoothController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\SummaryController;
use App\Http\Controllers\Api\SurveyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Event & Booth Management (Admin)
Route::apiResource('events', EventController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
Route::post('events/{event}/knowledge', [EventController::class, 'uploadKnowledge']);
Route::post('events/{event}/booths', [BoothController::class, 'store']);
Route::apiResource('booths', BoothController::class)->only(['show', 'destroy']);

// Survey (Public / Visitor-facing)
Route::post('survey/start', [SurveyController::class, 'start']);
Route::post('survey/{session}/answer', [SurveyController::class, 'answer']);
Route::get('survey/{session}', [SurveyController::class, 'show']);
Route::post('survey/{session}/complete', [SurveyController::class, 'complete']);

// Registration
Route::post('registration/start', [RegistrationController::class, 'startChat']);
Route::post('registration/ask', [RegistrationController::class, 'ask']);
Route::post('registration/submit', [RegistrationController::class, 'submit']);

// Summarization
Route::get('events/{event}/summary', [SummaryController::class, 'eventSummary']);
Route::get('booths/{booth}/summary', [SummaryController::class, 'boothSummary']);
Route::get('visitors/{visitor}/summary', [SummaryController::class, 'visitorSummary']);
Route::post('events/{event}/summary/regenerate', [SummaryController::class, 'regenerate']);
