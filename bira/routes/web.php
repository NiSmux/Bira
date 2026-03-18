<?php

use App\Http\Controllers\VartotojasController;
use App\Http\Controllers\ProfilisController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkItemController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\TeamController;

// --- PUBLIC ROUTES ---
// --- PUBLIC ROUTES ---
Route::get('/login', [VartotojasController::class , 'showLoginForm'])->name('login');
Route::post('/login', [VartotojasController::class , 'login'])->name('prisijungimas.jungtis');
Route::get('/register', [VartotojasController::class , 'showRegistrationForm'])->name('registracija.forma');
Route::post('/register', [VartotojasController::class , 'register'])->name('registracija.registruotis');

// --- APSAUGOTI MARŠRUTAI (tik prisijungusiems) ---
Route::middleware(['mano_apsauga'])->group(function () {

    Route::get('/', function () {
            return view('pagrindinis');
        }
        )->name('pagrindinis');

        Route::post('/logout', [VartotojasController::class , 'logout'])->name('atsijungti');


        // Teams Management
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.members.store');
        Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('teams.members.destroy');

        // Boards Management
        Route::get('/boards', [BoardController::class , 'index'])->name('boards.index');
        Route::get('/boards/create', [BoardController::class , 'create'])->name('boards.create');
        Route::post('/boards', [BoardController::class , 'store'])->name('boards.store');
        Route::get('/boards/{board}', [BoardController::class , 'show'])->name('boards.show');   
        Route::post('/boards/{board}/columns', [BoardController::class, 'addColumn'])->name('boards.columns.store');
        Route::patch('/boards/{board}/columns/{column}/reorder', [BoardController::class, 'reorderColumn'])->name('boards.columns.reorder');
        Route::patch('/boards/{board}/columns/{column}', [BoardController::class, 'updateColumn'])->name('boards.columns.update');
        Route::delete('/boards/{board}/columns/{column}', [BoardController::class, 'deleteColumn'])->name('boards.columns.destroy');

        // Forma naujai užduočiai
        Route::get('/boards/{board}/tasks/create', [WorkItemController::class , 'create'])
            ->name('boards.tasks.createTask');
        // Išsaugoti užduotį
        Route::post('/boards/{board}/tasks', [WorkItemController::class , 'store'])
            ->name('boards.tasks.store');
        // Užduoties ištrinimas
        Route::delete('/boards/{board}/tasks/{task}', [WorkItemController::class, 'destroy'])
            ->name('boards.tasks.destroy');
        // Užduoties redagavimas
        Route::get('/boards/{board}/tasks/{task}/edit', [WorkItemController::class, 'edit'])
            ->name('boards.tasks.edit');
        Route::put('/boards/{board}/tasks/{task}', [WorkItemController::class, 'update'])
            ->name('boards.tasks.update');
        Route::get('/boards/{board}/tasks/{task}', [WorkItemController::class, 'show'])
            ->name('boards.tasks.show');
        Route::patch('/boards/{board}/tasks/{task}/status', [WorkItemController::class, 'updateStatus'])
            ->name('boards.tasks.updateStatus');



        // Profile
        Route::get('/profile', [ProfilisController::class, 'show'])->name('profilis.rodyti');
        Route::get('/profile/edit', [ProfilisController::class, 'edit'])->name('profilis.redaguoti');
        Route::put('/profile', [ProfilisController::class, 'update'])->name('profilis.atnaujinti');
        Route::get('/profile/password', function () {
            return view('profilis.slaptazodis');
        })->name('profilis.slaptazodis');
        Route::put('/profile/password', [ProfilisController::class, 'keistiSlaptazodi'])->name('profilis.slaptazodis.keisti');
        Route::delete('/profile', [ProfilisController::class, 'destroy'])->name('profilis.trinti');
});