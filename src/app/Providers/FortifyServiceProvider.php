<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;


class FortifyServiceProvider extends ServiceProvider
{
    public function register():void
    {
        //
    }

    public function boot():void
    {
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });
        
        Fortify::loginView(fn() => view('auth.login'));
        Fortify::registerView(fn() => view('auth.register'));
        Fortify::createUsersUsing(CreateNewUser::class);
    }
}
