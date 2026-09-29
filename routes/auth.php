<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\ForcePasswordChangeController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RecoveryQuestionController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {


    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');

    // ====== Recuperación por preguntas de seguridad (offline) ======
    Route::get('recovery/method', [RecoveryQuestionController::class, 'showMethodSelection'])
                ->name('recovery.method');

    Route::get('recovery/email', [RecoveryQuestionController::class, 'showEmailForm'])
                ->name('recovery.email.show');

    Route::post('recovery/email', [RecoveryQuestionController::class, 'processEmail'])
                ->middleware('throttle:10,1')
                ->name('recovery.email.process');

    Route::get('recovery/questions', [RecoveryQuestionController::class, 'showQuestions'])
                ->name('recovery.questions.show');

    Route::post('recovery/questions', [RecoveryQuestionController::class, 'validateAnswers'])
                ->middleware('throttle:10,1')
                ->name('recovery.questions.validate');

    Route::get('recovery/reset/{token}', [RecoveryQuestionController::class, 'showResetForm'])
                ->name('recovery.reset.show');

    Route::post('recovery/reset', [RecoveryQuestionController::class, 'resetPassword'])
                ->name('recovery.reset.process');

    Route::get('recovery/locked', [RecoveryQuestionController::class, 'showLocked'])
                ->name('recovery.locked');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    // Cambio forzoso de contraseña tras reset por admin
    Route::get('force-password-change', [ForcePasswordChangeController::class, 'show'])
                ->name('auth.force-password-change.show');
    Route::post('force-password-change', [ForcePasswordChangeController::class, 'process'])
                ->name('auth.force-password-change.process');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});
