<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use tiFy\Container\ServiceProvider;
use tiFy\Plugins\Transaction\{
    ImportCommand as ImportCommandContract,
    ImportCommandStack as ImportCommandStackContract,
    ImportManager as ImportManagerContract,
    ImportRecorder as ImportRecorderContract,
    Transaction as TransactionContract
};

class TransactionServiceProvider extends ServiceProvider
{
    /**
     * Liste des noms de qualification des services fournis.
     * @internal requis. Tous les noms de qualification de services à traiter doivent être renseignés.
     * @var string[]
     */
    protected $provides = [
        'transaction',
        'transaction.import',
        'transaction.import.command',
        'transaction.import.command-stack',
        'transaction.import.recorder',
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
        $this->getContainer()->share('transaction', function (): TransactionContract {
            return new Transaction($this->getContainer()->get('console'), $this->getContainer()->get('app'));
        });

        $this->getContainer()->share('transaction.import', function (): ImportManagerContract {
            return new ImportManager();
        });

        $this->getContainer()->add('transaction.import.command', function (): ImportCommandContract {
            return new ImportCommand();
        });

        $this->getContainer()->add('transaction.import.command-stack', function (): ImportCommandStackContract {
            return new ImportCommandStack();
        });

        $this->getContainer()->add('transaction.import.recorder', function (): ImportRecorderContract {
            return new ImportRecorder();
        });
    }
}