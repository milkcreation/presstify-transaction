<?php

namespace tiFy\Plugins\Transaction;

use tiFy\App\Container\AppServiceProvider;

class TransactionServiceProvider extends AppServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->app->singleton('transaction', function() {return new Transaction();})->build();
    }
}