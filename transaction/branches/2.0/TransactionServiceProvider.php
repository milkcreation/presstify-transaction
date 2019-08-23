<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use tiFy\Container\ServiceProvider;

class TransactionServiceProvider extends ServiceProvider
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
        add_action('after_setup_theme', function () {
            $this->getContainer()->get('transaction');
        });
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->getContainer()->share('transaction', function() {
            return new Transaction(
                $this->getContainer()->get('console.application'),
                $this->getContainer()->get('app')
            );
        });
    }
}