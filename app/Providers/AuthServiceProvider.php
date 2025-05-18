<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Transacao;
use App\Policies\TransacaoPolicy;

class AuthServiceProvider extends ServiceProvider
{

    protected $policies = [
        Transacao::class => TransacaoPolicy::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
