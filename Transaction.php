<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use Psr\Container\ContainerInterface as Container;
use Symfony\Component\Console\Application as ConsoleApplication;
use tiFy\Plugins\Transaction\Contracts\{ImportCommand as ImportCommandContract,
    ImportCommandStack as ImportCommandStackContract,
    ImportRecords as ImportRecordsContract,
    Transaction as TransactionContract};
use tiFy\Support\Manager;

/**
 * @desc Extension PresstiFy de gestion de données de transaction.
 * @author Jordy Manner <jordy@milkcreation.fr>
 * @package tiFy\Plugins\Transaction
 * @version 2.0.37
 *
 * USAGE :
 * Activation :
 * ---------------------------------------------------------------------------------------------------------------------
 * Dans config/app.php ajouter \tiFy\Plugins\Transaction\TransactionServiceProvider à la liste des fournisseurs de
 * services chargés automatiquement par l'application.
 * ex.
 * <?php
 * ...
 * use tiFy\Plugins\Transaction\TransactionServiceProvider;
 * ...
 *
 * return [
 *      ...
 *      'providers' => [
 *          ...
 *          TransactionServiceProvider::class
 *          ...
 *      ]
 * ];
 *
 * Configuration :
 * ---------------------------------------------------------------------------------------------------------------------
 * Dans le dossier de config, créer le fichier transaction.php
 * @see /vendor/presstify-plugins/transaction/Resources/config/transaction.php Exemple de configuration
 */
class Transaction extends Manager implements TransactionContract
{
    /**
     * Instance du controleur d'application de commande console cli.
     * @var ConsoleApplication
     */
    protected $consoleApp;

    /**
     * CONSTRUCTEUR.
     *
     * @param ConsoleApplication $consoleApp
     * @param Container|null $container
     *
     * @return void
     */
    public function __construct(ConsoleApplication $consoleApp, ?Container $container = null)
    {
        $this->consoleApp = $consoleApp;

        parent::__construct($container);
    }

    /**
     * @inheritDoc
     */
    public function getConsoleApp(): ConsoleApplication
    {
        return $this->consoleApp;
    }

    /**
     * @inheritDoc
     */
    public function getImportCommand(string $name): ?ImportCommandContract
    {
        return $this->get("import.command.{$name}", null);
    }

    /**
     * @inheritDoc
     */
    public function getImportCommandStack(string $name): ?ImportCommandStackContract
    {
        return $this->get("import.command-stack.{$name}", null);
    }

    /**
     * @inheritDoc
     */
    public function getImportRecords(string $name): ?ImportRecordsContract
    {
        return $this->get("import.records.{$name}", null);
    }

    /**
     * @inheritDoc
     */
    public function registerImportCommand(
        ?string $name = null,
        ?ImportRecordsContract $records = null,
        array $params = []
    ): ?ImportCommandContract {
        /** @var ImportCommandContract $concrete */
        $concrete = $this->getContainer()
            ? $this->getContainer()->get('transaction.import.command')
            : new ImportCommand();

        if ($name) {
            $concrete->setName($name);
        }

        $command = $this->getConsoleApp()->add($concrete->setRecords($records)->setParams($params));
        $name = $command->getName();
        $alias = "import.command.{$name}";

        return $this->set($alias, $command)->get($alias, null);
    }

    /**
     * @inheritDoc
     */
    public function registerImportCommandStack(?string $name = null, array $stack = []): ?ImportCommandStackContract
    {
        /** @var ImportCommandStackContract $concrete */
        $concrete = $this->getContainer()
            ? $this->getContainer()->get('transaction.import.command-stack')
            : new ImportCommandStack();

        if ($name) {
            $concrete->setName($name);
        }

        $command = $this->getConsoleApp()->add($concrete->setStack($stack));
        $name = $command->getName();
        $alias = "import.command-stack.{$name}";

        return $this->set($alias, $command)->get($alias, null);
    }

    /**
     * @inheritDoc
     */
    public function registerImportRecords(string $name, array $params = []): ?ImportRecordsContract {
        /** @var ImportRecordsContract $concrete */
        $concrete = $this->getContainer()
            ? $this->getContainer()->get('transaction.import.records')
            : new ImportRecords();

        $alias = "import.records.{$name}";

        return $this->set($alias, $concrete->setManager($this)->setParams($params))->get($alias, null);
    }

    /**
     * @inheritDoc
     */
    public function resourcesDir(string $path = null): string
    {
        $path = $path ? '/' . ltrim($path, '/') : '';

        return (file_exists(__DIR__ . "/Resources{$path}"))
            ? __DIR__ . "/Resources{$path}"
            : '';
    }

    /**
     * @inheritDoc
     */
    public function resourcesUrl(string $path = null): string
    {
        $cinfo = class_info($this);
        $path = $path ? '/' . ltrim($path, '/') : '';

        return (file_exists($cinfo->getDirname() . "/Resources{$path}"))
            ? $cinfo->getUrl() . "/Resources{$path}"
            : '';
    }
}
