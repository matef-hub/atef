<?php

use App\Http\Controllers\ContractController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\RentController;
use App\Http\Controllers\language\LanguageController;
use App\Http\Controllers\pages\HomePage;
use Illuminate\Support\Facades\Route;

// Main Page Route
Route::get('/', [HomePage::class, 'index'])->name('pages-home');

// locale
Route::get('/lang/{locale}', [LanguageController::class, 'swap']);

Route::middleware([
  'auth:sanctum',
  config('jetstream.auth_session'),
  'verified',
])->group(function () {
  Route::get('/dashboard', fn() => redirect()->route('contracts.index'))->name('dashboard');

  Route::resource('contracts', ContractController::class);
  Route::resource('documents', DocumentController::class);
  Route::resource('rents', RentController::class);
});
