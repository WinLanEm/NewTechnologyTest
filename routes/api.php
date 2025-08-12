<?php
use App\Http\Controllers\TopCategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['logger','throttle:5,1'])->get('/appTopCategory', TopCategoryController::class);
