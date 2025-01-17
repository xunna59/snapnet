<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Customize the password reset URL
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url') . "/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // Handle unauthenticated exceptions globally
        $this->app->singleton(\Illuminate\Contracts\Debug\ExceptionHandler::class, function ($app) {
            return new class($app) extends \Illuminate\Foundation\Exceptions\Handler {
                /**
                 * Handle unauthenticated exceptions.
                 *
                 * @param  \Illuminate\Http\Request  $request
                 * @param  \Illuminate\Auth\AuthenticationException  $exception
                 * @return \Illuminate\Http\JsonResponse
                 */
                protected function unauthenticated($request, AuthenticationException $exception)
                {
                    return response()->json([
                        'message' => 'Authorization required. Please log in to access this resource.',
                    ], Response::HTTP_UNAUTHORIZED);
                }
            };
        });
    }
}
