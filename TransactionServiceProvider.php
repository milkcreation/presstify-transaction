<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use tiFy\Container\ServiceProvider;
use tiFy\Plugins\Transaction\Template\ImportListTable\{
    Contracts\FileBuilder as ImportListTableFileBuilderContract,
    FileBuilder as ImportListTableFileBuilder
};

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
                $this->getContainer()->get('console.controller.application'),
                $this->getContainer()->get('app')
            );
        });
        $this->registerImportListTable();
    }

    /**
     * Déclaration du template d'import.
     *
     * @return void
     */
    public function registerImportListTable(): void
    {
        $this->getContainer()->add(ImportListTableFileBuilderContract::class, function () {
            return new ImportListTableFileBuilder();
        });
    }
}