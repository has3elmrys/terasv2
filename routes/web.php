<?php

use App\Http\Controllers\AcceptInvitationController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware('throttle:10,1')->group(function () {
    Route::get('/invite/{token}', [AcceptInvitationController::class, 'show'])->name('invitations.accept');
    Route::post('/invite/{token}', [AcceptInvitationController::class, 'store'])->name('invitations.store');
});
