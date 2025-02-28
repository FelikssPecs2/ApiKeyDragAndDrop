<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController; // Import the controller
Route::post('/fetch-api', [ApiController::class, 'fetch']);
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Add this route to allow fetching API data
Route::post('/fetch', [ApiController::class, 'fetch']);