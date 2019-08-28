<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use Psr\Container\ContainerInterface as Container;
use Symfony\Component\Console\Application as ConsoleApplication;
use tiFy\Plugins\Transaction\Contracts\{
    ImportCommand as ImportCommandContract,
    ImportCommandStack as ImportCommandStackContract,
    ImportManager as ImportManagerContract,
    TransactionManager};
use tiFy\Support\Manager;

/**
 * @desc Extension PresstiFy de gestion de données de transaction.
 * @author Jordy Manner <jordy@milkcreation.fr>
 * @package tiFy\Plugins\Transaction
 * @version 2.0.22
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
class Transaction extends Manager implements TransactionManager
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
    public function registerImportCommand(
        string $name,
        ImportManagerContract $manager,
        array $params = []
    ): ?ImportCommandContract {
        $alias = "import.command.{$name}";

        return $this->set(
            $alias,
            $this->getConsoleApp()->add((new ImportCommand($name, $manager))->setParams($params))
        )->get($alias, null);
    }

    /**
     * @inheritDoc
     */
    public function registerImportStack(string $name, array $stack = []): ?ImportCommandStackContract
    {
        $alias = "import.stack.{$name}";

        return $this->set(
            $alias,
            $this->getConsoleApp()->add((new ImportCommandStack($name))->setStack($stack))
        )->get($alias, null);
    }

    /**
     * @inheritDoc
     */
    public function resourcesDir($path = ''): string
    {
        $path = $path ? '/' . ltrim($path, '/') : '';

        return (file_exists(__DIR__ . "/Resources{$path}"))
            ? __DIR__ . "/Resources{$path}"
            : '';
    }

    /**
     * @inheritDoc
     */
    public function resourcesUrl($path = ''): string
    {
        $cinfo = class_info($this);
        $path = $path ? '/' . ltrim($path, '/') : '';

        return (file_exists($cinfo->getDirname() . "/Resources{$path}"))
            ? $cinfo->getUrl() . "/Resources{$path}"
            : '';
    }
}
