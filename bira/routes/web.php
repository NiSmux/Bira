<?php

use App\Http\Controllers\VartotojasController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkItemController;
use App\Http\Controllers\BoardController;

// --- VIEŠI MARŠRUTAI (prieinami visiems) ---
Route::get('/prisijungimas', [VartotojasController::class , 'showLoginForm'])->name('login');
Route::post('/prisijungimas', [VartotojasController::class , 'login'])->name('prisijungimas.jungtis');
Route::get('/registracija', [VartotojasController::class , 'showRegistrationForm'])->name('registracija.forma');
Route::post('/registracija', [VartotojasController::class , 'register'])->name('registracija.registruotis');

// --- APSAUGOTI MARŠRUTAI (tik prisijungusiems) ---
Route::middleware(['mano_apsauga'])->group(function () {

    Route::get('/', function () {
            return view('pagrindinis');
        }
        )->name('pagrindinis');

        Route::post('/atsijungti', [VartotojasController::class , 'logout'])->name('atsijungti');


        // Boards Management
        Route::get('/boards', [BoardController::class , 'index'])->name('boards.index');
        Route::get('/boards/create', [BoardController::class , 'create'])->name('boards.create');
        Route::post('/boards', [BoardController::class , 'store'])->name('boards.store');
        Route::get('/boards/{board}', [BoardController::class , 'show'])->name('boards.show');   

        // Forma naujai užduočiai
        Route::get('/boards/{board}/tasks/create', [WorkItemController::class , 'create'])
            ->name('boards.tasks.createTask');
        // Išsaugoti užduotį
        Route::post('/boards/{board}/tasks', [WorkItemController::class , 'store'])
            ->name('boards.tasks.store');
        // Užduoties ištrinimas
        Route::delete('/boards/{board}/tasks/{task}', [WorkItemController::class, 'destroy'])
            ->name('boards.tasks.destroy');
        
});