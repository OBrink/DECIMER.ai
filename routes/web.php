<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\DecimerSegmentationController;
use App\Http\Controllers\DecimerController;
use App\Http\Controllers\ResultArchiveController;
use App\Http\Controllers\StoutController;
use App\Http\Controllers\SocketServerInitController;
use App\Http\Controllers\ProblemReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
})->name('home');

Route::get('/privacy_policy', function () {
    return view('privacy_policy');
})->name('privacy_policy');

Route::get('/impressum', function () {
    return view('impressum');
})->name('impressum');

Route::get('/about', function () {
    return view('about');
})->name('about');

/* Socket Server Initialisation Routes */
Route::post('socket-init', [SocketServerInitController::class, 'socketInitPost'])->name('socket.init.post');

/* File Upload Routes */
Route::get('file-upload', [FileUploadController::class, 'fileUpload'])->name('file.upload');
Route::post('file-upload', [FileUploadController::class, 'fileUploadPost'])->name('file.upload.post');

/* DECIMER Segmentation Routes */
Route::get('decimer-segmentation', [DecimerSegmentationController::class, 'DecimerSegmentation'])->name('decimer.segmentation');
Route::post('decimer-segmentation', [DecimerSegmentationController::class, 'DecimerSegmentationPost'])->name('decimer.segmentation.post');

/* DECIMER OCSR Routes */
Route::get('decimer-ocsr', [DecimerController::class, 'DecimerOCSR'])->name('decimer.ocsr');
Route::post('decimer-ocsr', [DecimerController::class, 'DecimerOCSRPost'])->name('decimer.ocsr.post');

/* DECIMER OCSR Routes */
Route::get('decimer-ocsr', [DecimerController::class, 'DecimerOCSR'])->name('decimer.ocsr');
Route::post('decimer-ocsr', [DecimerController::class, 'DecimerOCSRPost'])->name('decimer.ocsr.post');

/* Archive Generation Routes */
Route::get('create-archive', [ResultArchiveController::class, 'archiveCreation'])->name('archive.creation');
Route::post('create-archive', [ResultArchiveController::class, 'archiveCreationPost'])->name('archive.creation.post');

/* STOUT V2 Routes */
Route::get('stout-iupac', [StoutController::class, 'Stout'])->name('stout.iupac');
Route::post('stout-iupac', [StoutController::class, 'StoutPost'])->name('stout.iupac.post');

/* Problem Report Routes */
Route::get('problem-report', [ProblemReportController::class, 'ProblemReport'])->name('problem.report');
Route::post('problem-report', [ProblemReportController::class, 'ProblemReportPost'])->name('problem.report.post');

URL::forceScheme('https');
