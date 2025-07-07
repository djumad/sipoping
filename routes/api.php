<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\ImageDetectionController;
use App\Http\Controllers\JabatanController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\UnorController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CekJadwalHariIni;
use App\Http\Middleware\UserMiddleware;
use App\Http\Requests\UserLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix("/users")->group(function(){
    Route::post("/login" , [UserController::class , "login"]);
    Route::middleware(UserMiddleware::class)->group(function(){
        Route::get("/me" , [UserController::class ,"me"])->middleware(CekJadwalHariIni::class);
    });
});

Route::prefix("/jabatan")->middleware([UserMiddleware::class , AdminMiddleware::class])->group(function(){
    Route::get("" , [JabatanController::class , "index"]);
    Route::post("" , [JabatanController::class , "store"]);
    Route::get("/{id}", [JabatanController::class ,"show"]);
    Route::put("/{id}", [JabatanController::class ,"update"]);
    Route::delete("/{id}" , [JabatanController::class ,"destroy"]);
});

Route::prefix("/unor")->middleware([UnorController::class])->group(function(){
    Route::get("" , [UnorController::class , "index"]);
    Route::get("/{id}" , [UnorController::class ,"show"]);
    Route::post("", [UnorController::class ,"store"]);
    Route::put("/{id}" , [UnorController::class ,"update"]);
    Route::delete("/{id}" , [UnorController::class ,"destroy"]);
});

Route::prefix("/pegawai")->middleware([UserMiddleware::class , AdminMiddleware::class])->group(function(){
    Route::get("" , [PegawaiController::class , "index"]);
    Route::get("/{id}", [PegawaiController::class ,"show"]);
    Route::post("", [PegawaiController::class ,"store"]);
    Route::put("/{id}" , [PegawaiController::class ,"update"]);
    Route::delete("/{id}" , [PegawaiController::class ,"destroy"]);
});

Route::prefix("/absen")->middleware(UserMiddleware::class)->group(function(){
    Route::post("/{id}" , [AbsenController::class ,"store"]);
});