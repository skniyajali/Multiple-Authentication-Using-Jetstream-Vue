<?php

use Inertia\Inertia;
use Laravel\Fortify\Features;
use Laravel\Jetstream\Jetstream;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\Admin\UsersController;
use Laravel\Fortify\Http\Controllers\PasswordController;
use Laravel\Fortify\Http\Controllers\NewPasswordController;
use Laravel\Fortify\Http\Controllers\VerifyEmailController;
use Laravel\Fortify\Http\Controllers\RecoveryCodeController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\TwoFactorQrCodeController;
use Laravel\Fortify\Http\Controllers\PasswordResetLinkController;
use Laravel\Fortify\Http\Controllers\ProfileInformationController;
use Laravel\Fortify\Http\Controllers\ConfirmablePasswordController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Jetstream\Http\Controllers\Inertia\CurrentUserController;
use Laravel\Jetstream\Http\Controllers\Inertia\UserProfileController;
use Laravel\Jetstream\Http\Controllers\Inertia\ProfilePhotoController;
use Laravel\Fortify\Http\Controllers\ConfirmedPasswordStatusController;
use Laravel\Fortify\Http\Controllers\EmailVerificationPromptController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticationController;
use Laravel\Jetstream\Http\Controllers\Inertia\TermsOfServiceController;
use Laravel\Jetstream\Http\Controllers\Livewire\PrivacyPolicyController;
use Laravel\Fortify\Http\Controllers\EmailVerificationNotificationController;
use Laravel\Fortify\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Laravel\Jetstream\Http\Controllers\Inertia\OtherBrowserSessionsController;

Route::prefix('admin')->name('admin.')->group(function () {
    $enableViews = config('fortify.views', true);
    $limiter = config('fortify.limiters.login');
    $twoFactorLimiter = config('fortify.limiters.two-factor');
    $verificationLimiter = config('fortify.limiters.verification', '6,1');

    Route::get('/', function () {
        return Inertia::render('admin/Home', [
            'canLogin' => Route::has('admin.login'),
            'canRegister' => Route::has('admin.register'),
            'canGoToDashboard' => Auth::guard('admin')->check(),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    })->name('home');

    Route::middleware(['auth:admin', 'verified'])->get('/dashboard', function () {
        return Inertia::render('admin/AdminDashboard');
    })->name('dashboard');


    // Authentication...
    if ($enableViews) {
        Route::get('/login', [AuthenticatedSessionController::class, 'create'])
            ->middleware(['guest:admin'])
            ->name('login');
    }

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(array_filter([
            'guest:admin',
            $limiter ? 'throttle:' . $limiter : null,
    ]))->name('logins');


    // Registration...
    if (Features::enabled(Features::registration())) {
        if ($enableViews) {
            Route::get('/register', [RegisteredUserController::class, 'create'])
                ->middleware(['guest:admin'])
                ->name('register');
        }

        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->middleware(['guest:admin'])->name('registers');
    }

    // Password Reset...
    if (Features::enabled(Features::resetPasswords())) {
        if ($enableViews) {
            Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
                ->middleware(['guest:admin'])
                ->name('password.request');

            Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
                ->middleware(['guest:admin'])
                ->name('password.reset');
        }

        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware(['guest:admin'])
            ->name('password.email');

        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->middleware(['guest:admin'])
            ->name('password.update');
    }

    // Email Verification...
    if (Features::enabled(Features::emailVerification())) {
        if ($enableViews) {
            Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])
                ->middleware(['auth:admin'])
                ->name('verification.notice');
        }

        Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke'])
            ->middleware(['auth:admin', 'signed', 'throttle:' . $verificationLimiter])
            ->name('verification.verify');

        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware(['auth:admin', 'throttle:' . $verificationLimiter])
            ->name('verification.send');
    }

    Route::group(['middleware' => ['auth:admin', 'verified']], function () {
        // User & Profile...
        Route::get('/user/profile', [UserProfileController::class, 'show'])
            ->name('profile.show');

        Route::delete('/user/other-browser-sessions', [OtherBrowserSessionsController::class, 'destroy'])
            ->name('other-browser-sessions.destroy');

        Route::delete('/user/profile-photo', [ProfilePhotoController::class, 'destroy'])
            ->name('current-user-photo.destroy');

        if (Jetstream::hasAccountDeletionFeatures()) {
            Route::delete('/user', [CurrentUserController::class, 'destroy'])
                ->name('current-user.destroy');
        }

        Route::get('/users', [UsersController::class, 'show'])->name('all-users.show');
    });

    // Profile Information...
    if (Features::enabled(Features::updateProfileInformation())) {
        Route::put('/user/profile-information', [ProfileInformationController::class, 'update'])
            ->middleware(['auth:admin'])
            ->name('user-profile-information.update');
    }

    // Passwords...
    if (Features::enabled(Features::updatePasswords())) {
        Route::put('/user/password', [PasswordController::class, 'update'])
            ->middleware(['auth:admin'])
            ->name('user-password.update');
    }


    // Password Confirmation...
    if ($enableViews) {
        Route::get('/user/confirm-password', [ConfirmablePasswordController::class, 'show'])
            ->middleware(['auth:admin'])
            ->name('password.confirm');
    }

    Route::get('/user/confirmed-password-status', [ConfirmedPasswordStatusController::class, 'show'])
        ->middleware(['auth:admin'])
        ->name('password.confirmation');

    Route::post('/user/confirm-password', [ConfirmablePasswordController::class, 'store'])
        ->middleware(['auth:admin']);


    // Two Factor Authentication...
    if (Features::enabled(Features::twoFactorAuthentication())) {

        if ($enableViews) {
            Route::get('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
                ->middleware(['guest:admin'])
                ->name('two-factor.login');
        }

        Route::post('/two-factor-challenge', [TwoFactorAuthenticatedSessionController::class, 'store'])
            ->middleware(array_filter([
                'guest:admin',
                $twoFactorLimiter ? 'throttle:' . $twoFactorLimiter : null,
            ]))->name('two-factor.logins');

        $twoFactorMiddleware = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? ['auth:admin', 'password.confirm']
            : ['auth:admin'];

        Route::post('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'store'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.enable');

        Route::delete('/user/two-factor-authentication', [TwoFactorAuthenticationController::class, 'destroy'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.disable');

        Route::get('/user/two-factor-qr-code', [TwoFactorQrCodeController::class, 'show'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.qr-code');

        Route::get('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
            ->middleware($twoFactorMiddleware)
            ->name('two-factor.recovery-codes');

        Route::post('/user/two-factor-recovery-codes', [RecoveryCodeController::class, 'store'])
            ->middleware($twoFactorMiddleware)
            ->name('generateRecoveryCode');
    }

    if (Jetstream::hasTermsAndPrivacyPolicyFeature()) {
        Route::get('/terms-of-service', [TermsOfServiceController::class, 'show'])->name('terms.show');
        Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('policy.show');
    }

    //Logout the user
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
