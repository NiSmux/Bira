<?php

use App\Http\Controllers\VartotojasController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkItemController;

// --- VIEŠI MARŠRUTAI (prieinami visiems) ---
Route::get('/prisijungimas', [VartotojasController::class, 'showLoginForm'])->name('login');

Route::post('/prisijungimas', [VartotojasController::class, 'login'])->name('prisijungimas.jungtis');
Route::get('/registracija', [VartotojasController::class, 'showRegistrationForm'])->name('registracija.forma');
Route::post('/registracija', [VartotojasController::class, 'register'])->name('registracija.registruotis');


// --- APSAUGOTI MARŠRUTAI (tik prisijungusiems) ---

Route::middleware(['mano_apsauga'])->group(function () {
    
    Route::get('/', function () {
        return view('pagrindinis');
    })->name('pagrindinis');

    Route::post('/atsijungti', [VartotojasController::class, 'logout'])->name('atsijungti');

    Route::get('/lenta/{board}', [WorkItemController::class, 'show'])->name('lenta.rodyti');
    Route::get('/lenta/{board}/nauja-uzduotis', [WorkItemController::class, 'create'])->name('uzduotis.prideti');

    Route::post('/lenta/uzduotis/saugoti', [WorkItemController::class, 'store'])->name('uzduotis.saugoti');
});