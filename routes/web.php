<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserSettingsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;

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
Route::group(['middleware'=>'auth'],function () {

    // Home routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('Dashboard');

    // Log routes
    Route::get('/logs', [LogController::class, 'index'])->name('logsHome');
    Route::get('/logs/create', [LogController::class, 'create'])->name('createLog');
    Route::post('/logs/create', [LogController::class, 'store'])->name('createLog');
    Route::get('/logs/{logSlug}', [LogController::class, 'show'])->name('showLog');
    Route::get('/logs/edit/{logSlug}', [LogController::class, 'edit'])->name('editLog');
    Route::patch('/logs/update/{logSlug}', [LogController::class, 'update'])->name('updateLog');
    Route::get('/logs/delete/{logSlug}', [LogController::class, 'delete'])->name('deleteLog');
    Route::delete('/logs/delete/{logSlug}', [LogController::class, 'destroy'])->name('destroyLog');

    // Client routes
    Route::get('/clients', [ClientController::class, 'index'])->name('clientsHome');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('createClient');
    Route::post('/clients/create', [ClientController::class, 'store'])->name('createClient');
    Route::get('/clients/{clientSlug}', [ClientController::class, 'show'])->name('showClient');
    Route::get('/clients/edit/{logSlug}', [ClientController::class, 'edit'])->name('editClient');
    Route::patch('/clients/update/{logSlug}', [ClientController::class, 'update'])->name('updateClient');
    Route::delete('/clients/delete/{logSlug}', [ClientController::class, 'destroy'])->name('destroyClient');

    // User routes
    Route::get('/users', [UserController::class, 'index'])->name('usersHome');
    Route::get('/users/create', [UserController::class, 'create'])->name('createUser');
    Route::post('/users/create', [UserController::class, 'store'])->name('createUser');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('showUser');
    Route::get('/users/edit/{user}', [UserController::class, 'edit'])->name('editUser');
    Route::patch('/users/update/{user}', [UserController::class, 'update'])->name('updateUser');
    Route::get('/users/delete/{user}', [UserController::class, 'delete'])->name('deleteUser');
    Route::delete('/users/delete/{user}', [UserController::class, 'destroy'])->name('destroyUser');

    // User settings routes
    Route::get('/settings', [UserSettingsController::class, 'edit'])->name('editSettings');
    Route::put('/settings/update', [UserSettingsController::class, 'update'])->name('updateSettings');

    // Auth routes
    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Auth routes
Route::get('/', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::get('/forgot', [ResetPasswordController::class, 'create']);