<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use Exception;
use Symfony\Component\{Console\Command\Command as BaseCommand,
    Console\Exception\CommandNotFoundException,
    Console\Input\ArrayInput,
    Console\Input\InputInterface,
    Console\Input\InputOption,
    Console\Output\OutputInterface,
    Lock\Factory,
    Lock\Store\SemaphoreStore};
use tiFy\Plugins\Transaction\Contracts\ImportCommandStack as ImportCommandStackContract;
use tiFy\Support\ParamsBag;

/**
 * Suppression de la tâche cron
 * pkill -9 php
 */
class ImportCommandStack extends BaseCommand implements ImportCommandStackContract
{
    /**
     * Liste des arguments d'exécution des commandes.
     * @var ParamsBag
     */
    protected $args;

    /**
     * Liste des arguments d'exécution par défaut de toutes les commandes.
     * @var array
     */
    protected $defaults = [];

    /**
     * Liste des noms de qualification des commandes associées.
     * @var array
     */
    protected $stack = [];

    /**
     * CONSTRUCTEUR.
     *
     * @param string|null $name Nom de qualification de la commande.
     *
     * @return void
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this->args = new ParamsBag();
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this
            ->addOption('url', null, InputOption::VALUE_OPTIONAL, __('Url du site', 'tify'), '')
            ->addOption('release', null, InputOption::VALUE_OPTIONAL, __('Libération du verrou', 'tify'), false)
            ->addOption('archive', null, InputOption::VALUE_OPTIONAL, __('Archivage des fichiers', 'tify'), true);
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $url = $input->getOption('url') ?: '';

        $factory = new Factory(new SemaphoreStore());
        $lock = $factory->createLock($this->getName() . $url);

        if ($input->getOption('release') !== false) {
            $lock->release();
            return 0;
        } elseif (!$lock->acquire()) {
            $output->writeln(__('Cette commande est déjà en cours d\'exécution.', 'theme'));
            return 0;
        } else {
            foreach ($this->getStack() as $name) {
                try {
                    try {
                        $command = $this->getApplication()->find($name);
                    } catch (CommandNotFoundException $e) {
                        $output->writeln(sprintf(__('La commande "%s" est introuvable.', 'tify'), $name));
                        continue;
                    }

                    $command->run(new ArrayInput(array_merge($this->defaults, $this->args->all())), $output);
                } catch (Exception $e) {
                    $output->writeln(sprintf(__('Impossible d\'exécuter la commande "%s".', 'tify'), $name));
                    continue;
                }
                $output->writeln('');
            }
        }

        $lock->release();

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function addStack(string $name): ImportCommandStackContract
    {
        if (!in_array($name, $this->stack)) {
            array_push($this->stack, $name);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStack(): array
    {
        return $this->stack;
    }

    /**
     * @inheritDoc
     */
    public function setCommandArgs($name, array $args): ImportCommandStackContract
    {
        $this->args->set($name, $args);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setDefaultArgs(array $args): ImportCommandStackContract
    {
        $this->defaults = $args;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setStack(array $stack): ImportCommandStackContract
    {
        foreach ($stack as $name) {
            $this->addStack($name);
        }

        return $this;
    }
}