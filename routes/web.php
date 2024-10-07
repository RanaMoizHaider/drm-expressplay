<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/videos', [VideoController::class, 'index'])->name('video.index');  // Route for video index
Route::get('/video/upload', [VideoController::class, 'showUploadForm'])->name('video.upload.form');  // Route for video upload form
Route::post('/video/upload', [VideoController::class, 'uploadVideo'])->name('video.upload');  // Route for video upload
Route::get('/video/play/{token}', [VideoController::class, 'playVideo'])->name('video.play');  // Use token
Route::get('/video/stream/{token}', [VideoController::class, 'streamVideo'])->name('video.stream');  // Use token
