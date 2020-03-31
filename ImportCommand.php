<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use DateTimeZone;
use Exception;
use Symfony\Component\Console\{
    Input\InputInterface,
    Input\InputOption,
    Output\OutputInterface
};
use tiFy\Console\Command as BaseCommand;
use tiFy\Plugins\Transaction\Contracts\{ImportCommand as ImportCommandContract, ImportRecord, ImportRecorder};
use tiFy\Support\{DateTime, MessagesBag, ParamsBag};

class ImportCommand extends BaseCommand implements ImportCommandContract
{
    /**
     * Instance des messages de notification.
     * @var ParamsBag
     */
    private $messagesBag;

    /**
     * Instance du controleur d'enregistrement.
     * @var ImportRecorder
     */
    private $recorder;

    /**
     * Instance des paramètres.
     * @var ParamsBag
     */
    private $params;

    /**
     * CONSTRUCTEUR.
     *
     * @param string|null $name
     *
     * @return void
     */
    public function __construct(string $name = null)
    {
        parent::__construct($name);

        $this
            ->addOption('url', null, InputOption::VALUE_OPTIONAL, __('Url du site', 'tify'), '')
            ->addOption(
                'offset', null, InputOption::VALUE_OPTIONAL, __('Numéro d\'enregistrement de démarrage', 'tify'), 0
            )
            ->addOption(
                'length', null, InputOption::VALUE_OPTIONAL, __('Nombre d\'enregistrements à traiter', 'tify'), -1
            );
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->messages([
            'start'      => [
                '=====================================================================================================',
                sprintf(__('Import des %s.', 'tify'), $this->recorder()->labels()->plural()),
                '=====================================================================================================',
                __('Démarrage des opérations : %1$s', 'tify'),
                __('Total à traiter : %2$s', 'tify'),
                '',
            ],
            'item_start' => [
                '-----------------------------------------------------------------------------------------------------',
                sprintf(__('Import %s :', 'tify'), $this->recorder()->labels()->singular()) . ' %1$s/%2$s',
                '-----------------------------------------------------------------------------------------------------',
                __('Démarrage de l\'opération : %3$s', 'tify'),
                '',
            ],
            'item_end'   => [
                '',
                __('Fin de l\'opération : %s', 'tify'),
                '',
            ],
            'end'        => [
                '_____________________________________________________________________________________________________',
                '',
                sprintf(__('Résumé d\'import des %s :', 'tify'), $this->recorder()->labels()->plural()),
                '_____________________________________________________________________________________________________',
                '',
                __('Début des opérations : %1$s', 'tify'),
                __('Fin des opérations : %2$s', 'tify'),
                __('Traitement terminé en %3$s', 'tify'),
                __('%4$s enregistrement(s) réussi(s)', 'tify'),
                __('%5$s enregistrement(s) en échec', 'tify'),
                '_____________________________________________________________________________________________________',
                '_____________________________________________________________________________________________________',
            ],
        ]);

        $this->recorder()
            ->setBefore(function (ImportRecorder $recorder) use ($output) {
                $recorder->summary([
                    'class' => __CLASS__,
                    'type'  => 'cli',
                    'name'  => $this->getName(),
                ]);
                $output->writeln($this->messages(
                    'start',
                    '',
                    $this->getDate((int)$recorder->summary('start', 0)),
                    (int)$recorder->summary('count', 0)
                ));
            })
            ->setBeforeItem(function (ImportRecorder $recorder, ImportRecord $record, $key) use ($output) {
                $output->writeln(
                    $this->messages(
                        'item_start',
                        '',
                        $recorder->summary('index', 0) + 1,
                        $recorder->summary('count', 0),
                        $this->getDate()
                    )
                );
            })
            ->setAfterItem(function (ImportRecorder $recorder, ImportRecord $record, $key) use ($output) {
                foreach ($recorder->summary("items.{$key}.data.messages", []) as $level => $message) {
                    if ($level >= $this->getLevel()) {
                        $output->writeln($message);
                    }
                }

                $output->writeln($this->messages('item_end', '', $this->getDate()));
            })
            ->setAfter(function (ImportRecorder $recorder) use ($output) {
                $output->writeln($this->messages(
                    'end',
                    '',
                    $this->getDate((int)$recorder->summary('start', 0)),
                    $this->getDate((int)$recorder->summary('end', 0)),
                    date('H:i:s', $recorder->summary('duration', 0)),
                    $recorder->summary('success', 0),
                    $recorder->summary('failed', 0)
                ));
            })
            ->setOffset((int)$input->getOption('offset'))
            ->setLength((int)$input->getOption('length'))
            ->execute();

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function getDate(?int $time = null, ?DateTimeZone $tz = null): string
    {
        try {
            return (new DateTime($time, $tz))->format($this->getDateFormat());
        } catch (Exception $e) {
            return date($this->getDateFormat());
        }
    }

    /**
     * @inheritDoc
     */
    public function getDateFormat(): string
    {
        return (string)$this->params('date_format', 'Y-m-d H:i:s');
    }

    /**
     * @inheritDoc
     */
    public function getLevel(): int
    {
        return (int)MessagesBag::convertLevel($this->params('level', 'notice'));
    }

    /**
     * @inheritDoc
     */
    public function messages($key = null, $default = '', ...$args)
    {
        if(is_null($this->messagesBag)) {
            $this->messagesBag = new ParamsBag();
        }

        if (is_string($key)) {
            $message = $this->messagesBag->get($key, $default);
            return array_map(function ($message) use ($args) {
                return sprintf($message, ...$args);
            }, is_string($message) ? [$message] : $message);
        } elseif (is_array($key)) {
            return $this->messagesBag->set($key);
        } else {
            return $this->messagesBag;
        }
    }

    /**
     * @inheritDoc
     */
    public function params($key = null, $default = null)
    {
        if (is_null($this->params)) {
            $this->params = new ParamsBag();
        }

        if (is_string($key)) {
            return $this->params->get($key, $default);
        } elseif (is_array($key)) {
            return $this->params->set($key);
        } else {
            return $this->params;
        }
    }

    /**
     * @inheritDoc
     */
    public function recorder(): ?ImportRecorder
    {
        return $this->recorder;
    }

    /**
     * @inheritDoc
     */
    public function setParams(array $params): ImportCommandContract
    {
        $this->params($params);

        if ($description = $this->params()->pull('description')) {
            $this->setDescription($description);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRecorder(ImportRecorder $recorder): ImportCommandContract
    {
        $this->recorder = $recorder;

        return $this;
    }
}