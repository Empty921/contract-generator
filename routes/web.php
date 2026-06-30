<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TemplateController;

Route::get('/', [TemplateController::class, 'dashboard'])
    ->name('dashboard');

Route::get('/templates', [TemplateController::class, 'index'])
    ->name('templates.index');

Route::get('/templates/create', [TemplateController::class, 'create'])
    ->name('templates.create');

Route::post('/templates', [TemplateController::class, 'store'])
    ->name('templates.store');

Route::get('/templates/{id}', [TemplateController::class, 'show'])
    ->name('templates.show');

Route::post('/templates/{id}/generate', [TemplateController::class, 'generate'])
    ->name('templates.generate');

Route::delete('/templates/{id}', [TemplateController::class, 'destroy'])
    ->name('templates.destroy');