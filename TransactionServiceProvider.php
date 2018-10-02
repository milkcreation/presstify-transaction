<?php

namespace tiFy\Plugins\Transaction;

use tiFy\App\Container\AppServiceProvider;

class TransactionServiceProvider extends AppServiceProvider
{
    /**
     * {@inheritdoc}
     */
    protected $singletons = [
        Transaction::class
    ];

    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->app->resolve(Transaction::class);
    }
}