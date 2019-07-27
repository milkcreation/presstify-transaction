<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Import;

use DateTimeZone;
use Exception;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use tiFy\Console\Command;
use tiFy\Kernel\Params\ParamsBag;
use tiFy\Plugins\Transaction\Contracts\ImportManager;
use tiFy\Support\DateTime;

abstract class ImportCommand extends Command
{
    /**
     * Classe d'import (requis).
     *
     * @var string
     */
    protected $managerClass = '';

    /**
     * Cartographie de la liste des messages de traitement personnalisés.
     *
     * @var array
     */
    protected $messageMap = [];

    /**
     * Instance des messages de traitement.
     *
     * @var ParamsBag
     */
    private $message;

    /**
     * Instance du controleur d'import.
     *
     * @var ImportManager
     */
    private $manager;

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->addOption(
                'offset', null, InputOption::VALUE_OPTIONAL, __('Numéro d\'enregistrement de démarrage', 'theme'), 0
            )
            ->addOption(
                'length', null, InputOption::VALUE_OPTIONAL, __('Nombre d\'enregistrements à traiter', 'theme'), null
            );
    }

    /**
     * Récupération de la date au format datetime.
     *
     * @param string $time Date
     * @param DateTimeZone $tz
     *
     * @return string
     *
     * @throws Exception
     */
    public function datetime($time = null, $tz = null)
    {
        return (new DateTime($time, $tz))->toDateTimeString();
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->onStart();

        $output->writeln($this->message('start'));

        $output->writeln(array_map(function ($message) {
            return sprintf($message, $this->datetime());
        }, $this->message('start_datetime')));

        $offset = (int)$input->getOption('offset');
        $length = $input->getOption('length');
        $items  = array_slice($this->manager()->all(), $offset, $length, true);
        $count  = count($items);

        $output->writeln(array_map(function ($message) use ($count) {
            return sprintf($message, $count);
        }, $this->message('total_count')));

        $results = [];
        foreach ($items as $i => $item) {
            $this->onItemStart($item, $i);

            $output->writeln(array_map(function ($message) use ($i, $count) {
                return sprintf($message, $i, $count);
            }, $this->message('item_before')));

            $output->writeln(array_map(function ($message) {
                return sprintf($message, $this->datetime());
            }, $this->message('item_start_datetime')));

            $results[] = $res = $this->manager()->handleItem($item);

            foreach ($res['notices'] as $type => $notices) {
                foreach ($notices as $id => $notice) {
                    $output->writeln($notice['message']);
                }
            }

            $output->writeln(array_map(function ($message) {
                return sprintf($message, $this->datetime());
            }, $this->message('item_end_datetime')));

            $output->writeln(array_map(function ($message) use ($i, $count) {
                return sprintf($message, $i, $count);
            }, $this->message('item_after')));

            $this->onItemEnd($item, $i);
        }

        $output->writeln(array_map(function ($message) {
            return sprintf($message, $this->datetime());
        }, $this->message('end_datetime')));

        $output->writeln($this->message('end'));

        $this->onEnd($results);

        return 0;
    }

    /**
     * Récupération de l'instance du controleur d'import.
     *
     * @return ImportManager
     */
    protected function manager()
    {
        if (!$this->manager instanceof ImportManager) {
            $this->manager = new $this->managerClass();
        }

        return $this->manager;
    }

    /**
     * Récupération des messages de sortie ou d'un message de sortie.
     *
     * @param null|string $key Clé d'indice du message
     * @param mixed $default Valeur de retour par défaut
     *
     * @return array
     */
    public function message($key = null, $default = '')
    {
        if (!$this->message instanceof ParamsBag) {
            $this->message = params(array_merge(
                [
                    'start'               => [
                        '====================================',
                        __('Import des élèments.', 'tify'),
                        '====================================',
                    ],
                    'start_datetime'      => __('Démarrage des opérations : %s', 'tify'),
                    'total_count'         => [
                        __('Total des élèments à traiter : %d', 'tify'),
                        '',
                    ],
                    'item_before'         => [
                        '------------------------------------',
                        __('Import de l\'élèment %d/%d', 'tify'),
                        '------------------------------------'
                    ],
                    'item_start_datetime' => [
                        __('Démarrage de l\'opération : %s', 'tify'),
                        ''
                    ],
                    'item_end_datetime'   => [
                        '',
                        __('Fin de l\'opération : %s', 'tify'),
                    ],
                    'item_after'          => [
                        '',
                    ],
                    'end_datetime'        => [
                        '',
                        'Import des éléments terminé.',
                        __('Fin des opérations : %s', 'tify')
                    ],
                    'end'                 => "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                ],
                $this->messageMap
            ));
        }

        return  is_null($key) ? $this->message : Arr::wrap($this->message->get($key, $default));
    }

    /**
     * Action lancée à l'issue du traitement.
     *
     * @param array $results
     *
     * @return void
     */
    public function onEnd($results) {}

    /**
     * Action lancée avant le traitement d'un élément d'import.
     *
     * @param ImportFactory $item Instance de l'élément d'import.
     * @param int $key Indice de l'élément d'import.
     *
     * @return void
     */
    public function onItemEnd($item, $key) {}

    /**
     * Action lancée avant le traitement d'un élément d'import.
     *
     * @param ImportFactory $item Instance de l'élément d'import.
     * @param int $key Indice de l'élément d'import.
     *
     * @return void
     */
    public function onItemStart($item, $key) {}

    /**
     * Action lancée à l'issue du traitement.
     *
     * @return void
     */
    public function onStart() {}
}