<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use Exception;
use Psr\Container\ContainerInterface as Container;
use tiFy\Contracts\Console\Console;
use tiFy\Plugins\Transaction\Contracts\{ImportManager as ImportManagerContract, Transaction as TransactionContract};

/**
 * @desc Extension PresstiFy de gestion de données de transaction.
 * @author Jordy Manner <jordy@milkcreation.fr>
 * @package tiFy\Plugins\Transaction
 * @version 2.0.47
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
class Transaction implements TransactionContract
{
    /**
     * Cartographie des services.
     * @var array
     */
    protected $builtInClasses = [
        'import'               => ImportManager::class,
        'import.command'       => ImportCommand::class,
        'import.command-stack' => ImportCommandStack::class,
        'import.recorder'      => ImportRecorder::class,
    ];

    /**
     * Instance du controleur d'application de commande console cli.
     * @var Console
     */
    protected $console;

    /**
     * Instance du conteneur d'injection de dépendances.
     * @var Container
     */
    protected $container;

    /**
     * CONSTRUCTEUR.
     *
     * @param Console $console
     * @param Container|null $container
     *
     * @return void
     */
    public function __construct(Console $console, ?Container $container = null)
    {
        $this->console = $console;

        if ($container) {
            $this->setContainer($container);
        }

        $this->import();
    }

    /**
     * @inheritDoc
     */
    public function dir(string $path = null): string
    {
        $path = $path ? '/' . ltrim($path, '/') : '';

        return (file_exists(__DIR__ . "/Resources{$path}"))
            ? __DIR__ . "/Resources{$path}"
            : '';
    }

    /**
     * @inheritDoc
     */
    public function getConsole(): Console
    {
        return $this->console;
    }

    /**
     * @inheritDoc
     */
    public function getContainer(): ?Container
    {
        return $this->container;
    }

    /**
     * @inheritDoc
     */
    public function import(): ?ImportManagerContract
    {
        try {
            return $this->resolve('import')->setTransaction($this);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @inheritDoc
     */
    public function resolve(string $alias): object
    {
        $concrete = $this->getContainer()
            ? $this->getContainer()->get("transaction.{$alias}") : (($classname = $this->builtInClasses[$alias] ?? null) ? new $classname : null);

        if ($concrete) {
            return $concrete;
        }

        throw new Exception(sprintf(__('Impossible de retrouver le service de transaction [%s]'), $alias));
    }

    /**
     * @inheritDoc
     */
    public function setContainer(Container $container): TransactionContract
    {
        $this->container = $container;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function url(string $path = null): string
    {
        $cinfo = class_info($this);
        $path = $path ? '/' . ltrim($path, '/') : '';

        return (file_exists($cinfo->getDirname() . "/Resources{$path}"))
            ? $cinfo->getUrl() . "/Resources{$path}"
            : '';
    }
}
