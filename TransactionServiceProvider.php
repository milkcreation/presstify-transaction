<?php

namespace tiFy\Plugins\Transaction;

use tiFy\App\Container\AppServiceProvider;

class TransactionServiceProvider extends AppServiceProvider
{
    /**
     * Liste des noms de qualification des services fournis.
     * @internal requis. Tous les noms de qualification de services à traiter doivent être renseignés.
     * @var string[]
     */
    protected $provides = [
        'transaction'
    ];

    /**
     * @inheritdoc
     */
    public function boot()
    {
        add_action('after_setup_tify', function () {
            $this->getContainer()->get('transaction');
        });
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->getContainer()->share('transaction', function() {
            return new Transaction();
        });
    }
}