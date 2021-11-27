<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
use App\Models\ConnectedAccount;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Config;
use App\Policies\ConnectedAccountPolicy;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        ConnectedAccount::class => ConnectedAccountPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if(request()->is('admin/*')){
            ResetPassword::createUrlUsing(function ($notifiable, string $token) {
                return url(route('admin.password.reset', [
                    'token' => $token,
                    'email' => $notifiable->getEmailForPasswordReset(),
                ], false));
            });

            VerifyEmail::createUrlUsing(function ($notifiable) {
                return URL::temporarySignedRoute(
                    'admin.verification.verify',
                    Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                    [
                        'id' => $notifiable->getKey(),
                        'hash' => sha1($notifiable->getEmailForVerification()),
                    ]
                );
            });


        }
    }
}
