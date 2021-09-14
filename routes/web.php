<?php

use App\Http\Controllers\SSOControllers;
use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});

Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get("/redirect", [SSOControllers::class,'redirect'])->name('redirect');
Route::get("/callback", [SSOControllers::class,'callback'])->name('callback');
Route::get("/login_with_sso", [SSOControllers::class,'login_with_sso'])->name('login_with_sso');