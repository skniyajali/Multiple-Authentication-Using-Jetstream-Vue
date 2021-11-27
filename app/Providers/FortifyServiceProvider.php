<?php

namespace App\Providers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use App\Http\Responses\LoginResponse;
use Illuminate\Support\Facades\Route;
use App\Actions\Fortify\CreateNewUser;
use App\Http\Responses\LogoutResponse;
use Illuminate\Support\ServiceProvider;
use App\Http\Responses\RegisterResponse;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Support\Facades\RateLimiter;
use App\Http\Responses\PasswordResetResponse;
use App\Http\Responses\TwoFactorLoginResponse;
use App\Http\Responses\FailedTwoFactorLoginResponse;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\SuccessfulPasswordResetLinkRequestResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Contracts\PasswordResetResponse as PasswordResetResponseContract;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse as FailedTwoFactorLoginResponseContract;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (request()->is('admin/*')) {
            config()->set('fortify.guard', 'admin');
            config()->set('fortify.passwords', 'admins');
            config()->set('fortify.home', 'admin/dashboard/');
            config()->set('fortify.prefix', 'admin.');
            config()->set('fortify.path', 'admin');
        }

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if(request()->is('admin/*')){
            Fortify::loginView(function () {
                return Inertia::render('admin/Auth/Login',[
                    'canResetPassword' => Route::has('admin.password.request'),
                    'status' => session('status'),
                ]);
            });
            Fortify::registerView(function () {
                return Inertia::render('admin/Auth/Register');
            });

            Fortify::twoFactorChallengeView(function () {
                return Inertia::render('admin/Auth/TwoFactorChallenge');
            });

            Fortify::requestPasswordResetLinkView(function () {
                return Inertia::render('admin/Auth/ForgotPassword', [
                    'status' => session('status'),
                ]);
            });

            Fortify::resetPasswordView(function ($request) {
                // dd($request->token);
                return Inertia::render('admin/Auth/ResetPassword', [
                    'email' => $request->input('email'),
                    'token' => $request->route('token'),
                ]);
            });

            Fortify::verifyEmailView(function () {
                return Inertia::render('admin/Auth/VerifyEmail', [
                    'status' => session('status'),
                ]);
            });

            Fortify::confirmPasswordView(function () {
                return Inertia::render('admin/Auth/ConfirmPassword');
            });
        }

        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });



        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
        $this->app->singleton(TwoFactorLoginResponseContract::class, TwoFactorLoginResponse::class);
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
        $this->app->singleton(SuccessfulPasswordResetLinkRequestResponseContract::class, SuccessfulPasswordResetLinkRequestResponse::class);
        $this->app->singleton(PasswordResetResponseContract::class, PasswordResetResponse::class);
        $this->app->singleton(FailedTwoFactorLoginResponseContract::class, FailedTwoFactorLoginResponse::class);

    }
}
