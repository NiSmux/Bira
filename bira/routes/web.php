<?php

use App\Http\Controllers\VartotojasController;
use App\Http\Controllers\ProfilisController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkItemController;
use App\Http\Controllers\BoardController;
use App\Http\Controllers\BacklogController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BoardSubTeamController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\ThemeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PlanningPokerController;
use App\Http\Controllers\SprintController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MyTasksController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\TimeLogController;

// --- PUBLIC ROUTES ---
Route::get('/login', [VartotojasController::class , 'showLoginForm'])->name('login');
Route::post('/login', [VartotojasController::class , 'login'])->name('prisijungimas.jungtis');
Route::get('/register', [VartotojasController::class , 'showRegistrationForm'])->name('registracija.forma');
Route::post('/register', [VartotojasController::class , 'register'])->name('registracija.registruotis');

// --- APSAUGOTI MARŠRUTAI (tik prisijungusiems) ---
Route::middleware(['mano_apsauga'])->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('pagrindinis');
        Route::post('/logout', [VartotojasController::class , 'logout'])->name('atsijungti');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
        Route::match(['get', 'post'], '/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');


        // Themes
        Route::get('/themes', [ThemeController::class, 'index'])->name('themes.index');
        Route::post('/themes', [ThemeController::class, 'update'])->name('themes.update');

        // Feedback
        Route::get('/feedback/feature-requests', [FeedbackController::class, 'featureRequests'])->name('feedback.feature-requests');
        Route::get('/feedback/bug-report', [FeedbackController::class, 'bugReport'])->name('feedback.bug-report');
        Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');

        // Teams Management
        Route::get('/teams', [TeamController::class, 'index'])->name('teams.index');
        Route::get('/teams/create', [TeamController::class, 'create'])->name('teams.create');
        Route::post('/teams', [TeamController::class, 'store'])->name('teams.store');
        Route::get('/teams/{team}', [TeamController::class, 'show'])->name('teams.show');
        Route::post('/teams/{team}/members', [TeamController::class, 'addMember'])->name('teams.members.store');
        Route::delete('/teams/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('teams.members.destroy');
        Route::patch('/teams/{team}/default-type', [TeamController::class, 'updateDefaultType'])->name('teams.default-type.update');
        Route::post('/teams/{team}/item-types', [TeamController::class, 'storeItemType'])->name('teams.item-types.store');
        Route::patch('/teams/{team}/item-types/{itemType}', [TeamController::class, 'updateItemType'])->name('teams.item-types.update');
        Route::delete('/teams/{team}/item-types/{itemType}', [TeamController::class, 'destroyItemType'])->name('teams.item-types.destroy');

        // AJAX: get team members for board creation form
        Route::get('/api/teams/{team}/members', [BoardController::class, 'getTeamMembers'])->name('api.teams.members');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // My Active Tasks
    Route::get('/my-tasks', [MyTasksController::class, 'index'])->name('my-tasks.index');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/month-data', [CalendarController::class, 'monthData'])->name('calendar.monthData');
    Route::get('/calendar/day/{date}', [CalendarController::class, 'dayData'])->name('calendar.dayData');
    Route::post('/calendar/notes', [CalendarController::class, 'storeNote'])->name('calendar.notes.store');
    Route::post('/calendar/time-logs', [CalendarController::class, 'storeTimeLog'])->name('calendar.timeLogs.store');
    Route::delete('/calendar/time-logs/{timeLog}', [CalendarController::class, 'destroyTimeLog'])->name('calendar.timeLogs.destroy');
    Route::get('/calendar/board-tasks/{board}', [CalendarController::class, 'getBoardTasks'])->name('calendar.board-tasks');

    
    // Backlog (Global view)
    Route::get('/backlog', [BacklogController::class, 'index'])->name('backlog.index');
        // Boards Management
        Route::get('/boards', [BoardController::class , 'index'])->name('boards.index');
        Route::get('/boards/create', [BoardController::class , 'create'])->name('boards.create');
        Route::post('/boards', [BoardController::class , 'store'])->name('boards.store');
        Route::get('/boards/{board}/metrics', [BoardController::class, 'metricsData'])->name('boards.metrics');
        Route::get('/boards/{board}', [BoardController::class , 'show'])->name('boards.show');
        Route::delete('/boards/{board}', [BoardController::class, 'destroy'])->name('boards.destroy');

        // Board Settings & Member Management
        Route::get('/boards/{board}/settings', [BoardController::class, 'settings'])->name('boards.settings');
        Route::patch('/boards/{board}/settings/mode', [BoardController::class, 'updateMode'])->name('boards.update_mode');
        Route::patch('/boards/{board}/settings/sp-rate', [BoardController::class, 'updateSpRate'])->name('boards.update_sp_rate');
        Route::post('/boards/{board}/members', [BoardController::class, 'addBoardMember'])->name('boards.members.store');
        Route::patch('/boards/{board}/members/{user}', [BoardController::class, 'updateBoardMemberRole'])->name('boards.members.updateRole');
        Route::delete('/boards/{board}/members/{user}', [BoardController::class, 'removeBoardMember'])->name('boards.members.destroy');

        // Board Sub-Teams
        Route::post('/boards/{board}/sub-teams', [BoardSubTeamController::class, 'store'])->name('boards.sub-teams.store');
        Route::patch('/boards/{board}/sub-teams/{subTeam}', [BoardSubTeamController::class, 'update'])->name('boards.sub-teams.update');
        Route::delete('/boards/{board}/sub-teams/{subTeam}', [BoardSubTeamController::class, 'destroy'])->name('boards.sub-teams.destroy');
        Route::post('/boards/{board}/sub-teams/{subTeam}/members', [BoardSubTeamController::class, 'addMember'])->name('boards.sub-teams.members.store');
        Route::delete('/boards/{board}/sub-teams/{subTeam}/members/{user}', [BoardSubTeamController::class, 'removeMember'])->name('boards.sub-teams.members.destroy');

        // Sprints
        Route::get('/boards/{board}/sprints/history', [SprintController::class, 'history'])->name('boards.sprints.history');
        Route::post('/boards/{board}/sprints', [SprintController::class, 'store'])->name('boards.sprints.store');
        Route::patch('/boards/{board}/sprints/{sprint}', [SprintController::class, 'update'])->name('boards.sprints.update');
        Route::delete('/boards/{board}/sprints/{sprint}', [SprintController::class, 'destroy'])->name('boards.sprints.destroy');
        Route::post('/boards/{board}/sprints/{sprint}/plan', [SprintController::class, 'plan'])->name('boards.sprints.plan');
        Route::post('/boards/{board}/sprints/{sprint}/start', [SprintController::class, 'start'])->name('boards.sprints.start');
        Route::post('/boards/{board}/sprints/{sprint}/complete', [SprintController::class, 'complete'])->name('boards.sprints.complete');
        Route::post('/boards/{board}/sprints/{sprint}/deliver', [SprintController::class, 'deliver'])->name('boards.sprints.deliver');
        Route::post('/boards/{board}/sprints/{sprint}/items', [SprintController::class, 'addItem'])->name('boards.sprints.items.store');
        Route::delete('/boards/{board}/sprints/{sprint}/items/{item}', [SprintController::class, 'removeItem'])->name('boards.sprints.items.destroy');

        // Board Columns
        Route::post('/boards/{board}/columns', [BoardController::class, 'addColumn'])->name('boards.columns.store');
        Route::patch('/boards/{board}/columns/{column}/reorder', [BoardController::class, 'reorderColumn'])->name('boards.columns.reorder');
        Route::patch('/boards/{board}/columns/{column}', [BoardController::class, 'updateColumn'])->name('boards.columns.update');
        Route::delete('/boards/{board}/columns/{column}', [BoardController::class, 'deleteColumn'])->name('boards.columns.destroy');

        // Board Tags
        Route::post('/boards/{board}/tags', [\App\Http\Controllers\TagController::class, 'store'])->name('boards.tags.store');
        Route::patch('/boards/{board}/tags/{tag}', [\App\Http\Controllers\TagController::class, 'update'])->name('boards.tags.update');
        Route::post('/boards/{board}/tags/batch-delete', [\App\Http\Controllers\TagController::class, 'destroyBatch'])->name('boards.tags.batch_delete');
        Route::delete('/boards/{board}/tags/{tag}', [\App\Http\Controllers\TagController::class, 'destroy'])->name('boards.tags.destroy');
        Route::post('/boards/{board}/tasks/{task}/tags', [\App\Http\Controllers\TagController::class, 'attach'])->name('boards.tasks.tags.attach');
        Route::delete('/boards/{board}/tasks/{task}/tags/{tag}', [\App\Http\Controllers\TagController::class, 'detach'])->name('boards.tasks.tags.detach');

        // Task Comments
        Route::post('/boards/{board}/tasks/{task}/comments', [\App\Http\Controllers\WorkItemCommentController::class, 'store'])->name('boards.tasks.comments.store');
        Route::delete('/boards/{board}/tasks/{task}/comments/{comment}', [\App\Http\Controllers\WorkItemCommentController::class, 'destroy'])->name('boards.tasks.comments.destroy');

        // Task Time Logs
        Route::post('/boards/{board}/tasks/{task}/time-logs', [TimeLogController::class, 'store'])->name('boards.tasks.timeLogs.store');
        Route::delete('/boards/{board}/tasks/{task}/time-logs/{timeLog}', [TimeLogController::class, 'destroy'])->name('boards.tasks.timeLogs.destroy');

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
        Route::get('/boards/{board}/tasks/{task}/quick-edit-data', [WorkItemController::class, 'quickEditData'])
            ->name('boards.tasks.quickEditData');



        // Profile
        Route::get('/profile/edit', [ProfilisController::class, 'edit'])->name('profilis.redaguoti');
        Route::get('/profile/password', function () {
            return view('profilis.slaptazodis');
        })->name('profilis.slaptazodis');
        Route::get('/profile/{id?}', [ProfilisController::class, 'show'])->name('profilis.rodyti');
        Route::put('/profile', [ProfilisController::class, 'update'])->name('profilis.atnaujinti');
        Route::put('/profile/password', [ProfilisController::class, 'keistiSlaptazodi'])->name('profilis.slaptazodis.keisti');
        Route::delete('/profile', [ProfilisController::class, 'destroy'])->name('profilis.trinti');

        // Reports
        Route::get('/boards/{board}/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/boards/{board}/reports/burndown/{sprint?}', [ReportController::class, 'burndown'])->name('reports.burndown');
        Route::get('/boards/{board}/reports/velocity', [ReportController::class, 'velocity'])->name('reports.velocity');

        // Planning Poker
        Route::get('/poker', [PlanningPokerController::class, 'index'])->name('poker.index');
        Route::get('/poker/create', [PlanningPokerController::class, 'create'])->name('poker.create');
        Route::post('/poker', [PlanningPokerController::class, 'store'])->name('poker.store');
        Route::get('/poker/{session}', [PlanningPokerController::class, 'show'])->name('poker.show');
        Route::post('/poker/{session}/items/{item}/vote', [PlanningPokerController::class, 'vote'])->name('poker.vote');
        Route::post('/poker/{session}/items/{item}/restart', [PlanningPokerController::class, 'restartTask'])->name('poker.restartTask');
        Route::post('/poker/{session}/items/{item}/next', [PlanningPokerController::class, 'nextTask'])->name('poker.nextTask');
        Route::post('/poker/{session}/complete', [PlanningPokerController::class, 'complete'])->name('poker.complete');
        Route::get('/poker/{session}/results', [PlanningPokerController::class, 'results'])->name('poker.results');
        Route::post('/poker/{session}/save-points', [PlanningPokerController::class, 'savePoints'])->name('poker.savePoints');
        Route::get('/poker/board/{board}/items', [PlanningPokerController::class, 'boardItems'])->name('poker.boardItems');
});