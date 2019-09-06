<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use tiFy\Contracts\{
    Log\Logger as LoggerContract,
    Support\LabelsBag as LabelsBagContract,
    Support\MessagesBag as MessagesBagContract
};
use tiFy\Plugins\Parser\{Contracts\Reader as ReaderContract, Exceptions\ReaderException, Reader};
use tiFy\Plugins\Transaction\{Contracts\ImportRecord as ImportRecordContract,
    Contracts\ImportRecords as ImportRecordsContract};
use tiFy\Support\{Collection, DateTime, LabelsBag, MessagesBag, ParamsBag};

class ImportRecords extends Collection implements ImportRecordsContract
{
    /**
     * Liste des fonctions de traitement de l'import.
     * @var callable[][]
     */
    protected $callable = [
        'after'       => [],
        'after_item'  => [],
        'before'      => [],
        'before_item' => [],
    ];

    /**
     * Liste des éléments du fichier.
     * @var ImportRecordContract[]
     */
    protected $items = [];

    /**
     * Instance du gestionnaire des intitulés.
     * @var LabelsBag|null
     */
    protected $labels;

    /**
     * Nombre d'élément à traiter.
     * @var int|null
     */
    protected $length;

    /**
     * Instance du gestionnaire de journalisation.
     * @var LoggerContract|false|null
     */
    protected $logger;

    /**
     * Indice de l'enregistrement de démarrage.
     * @var int
     */
    protected $offset = 0;

    /**
     * Instance du controleur de gestion des paramètres d'import.
     * @var ParamsBag
     */
    protected $params;

    /**
     * Instance du gestionnaire de récupération des enregistrements.
     * @var ReaderContract|null
     */
    protected $reader;

    /**
     * Instance du résumé de traitement.
     * @var ParamsBag
     */
    protected $summary;

    /**
     * CONSTRUCTEUR.
     *
     * @param array $params Liste des paramètres.
     *
     * @return void
     *
     * @throws ReaderException
     */
    public function __construct($params = [])
    {
        $this
            ->setLabels(LabelsBag::createFromAttrs([]))
            ->setParams($params);

        $this->summary = new ParamsBag();
    }

    /**
     * @inheritDoc
     */
    public static function createFromPath(string $path, $params = []): ImportRecordsContract
    {
       return (new static($params))->fromPath($path);
    }

    /**
     * @inheritDoc
     */
    public static function createFromReader(ReaderContract $reader, $params = []): ImportRecordsContract
    {
        return (new static())->setParams($params)->setReader($reader);
    }

    /**
     * @inheritDoc
     */
    public function callAfter(): ImportRecordsContract
    {
        foreach ($this->callable['after'] as $callable) {
            $callable($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function callAfterItem(ImportRecordContract $record, $key): ImportRecordsContract
    {
        foreach ($this->callable['after_item'] as $callable) {
            $callable($this, $record, $key);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function callBefore(): ImportRecordsContract
    {
        foreach ($this->callable['before'] as $callable) {
            $callable($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function callBeforeItem(ImportRecordContract $record, $key): ImportRecordsContract
    {
        foreach ($this->callable['before_item'] as $callable) {
            $callable($this, $record, $key);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function execute(): ImportRecordsContract
    {
        $start = time() + (new DateTime())->getOffset();

        $this->fetch();

        $count = $this->count();

        $this->summary([
            'index' => 0,
            'start' => $start,
            'count' => $count,
        ]);

        $this->callBefore();

        $this->logger(
            'info',
            sprintf(__('-------- Démarrage de l\'import des %s --------', 'tify'), $this->labels()->plural()),
            $this->summary()->all()
        );

        foreach ($this->collect()->keys() as $key) {
            $this->executeRecord($key);
        }

        $end = time() + (new DateTime())->getOffset();
        $this->summary([
            'end'      => $end,
            'duration' => $end - $start,
        ]);

        $this->callAfter();

        $this->logger(
            'info',
            sprintf(__('-------- Fin de l\'import des %s --------', 'tify'), $this->labels()->plural()),
            $this->summary()->all()
        );

        $this->summary->clear();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function executeRecord($key): ImportRecordsContract
    {
        if ($record = $this->get($key)) {
            $this->callBeforeItem($record, $key);

            $this->summary([
                "items.{$key}" => $record->setIndex((int)$this->summary('index', 0))->execute()->getResults()
            ]);

            $this->summary(['index' => $this->summary('index', 0) + 1]);

            if (!$this->summary("items.{$key}.success")) {
                $this->summary(['failed' => $this->summary('failed', 0) + 1]);
            } else {
                $this->summary(['success' => $this->summary('success', 0) + 1]);
            }

            $this->callAfterItem($record, $key);

            $messages = $this->summary("items.{$key}.data.messages", null);

            if ($messages instanceof MessagesBag) {
                foreach ($messages->fetch() as $level => $item) {
                    $this->logger($item['level'], $item['message'], $item['data']);
                }
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetch(): ImportRecordsContract
    {
        if ($reader = $this->reader()) {
            $this->set(
                $reader
                    ->setOffset($this->getOffset())
                    ->setPage(1)
                    ->setPerPage($this->getLength())
                    ->fetch()
            );
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fromPath(string $path): ImportRecordsContract
    {
        try {
            $this->setReader(Reader::createFromPath($path));
        } catch (ReaderException $e) {
            if ($this->params('asserts', true)) {
                throw $e;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * @inheritDoc
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @inheritDoc
     */
    public function labels($key = null, $default = null)
    {
        if (is_string($key)) {
            return $this->labels->get($key, $default);
        } elseif (is_array($key)) {
            return $this->labels->set($key);
        } else {
            return $this->labels;
        }
    }

    /**
     * @inheritDoc
     */
    public function logger($level = null, string $message = '', array $context = []): ?LoggerContract
    {
        if (is_null($this->logger)) {
            if ($logger = $this->params()->pull('logger', true)) {
                if (!$logger instanceof LoggerContract) {
                    $params = is_array($logger) ? $logger : [];
                    /** @var LoggerContract $logger */
                    $logger = app('logger');
                    $logger->setParams($params);
                }
                $this->setLogger($logger);
            } else {
                return null;
            }
        }

        if (is_null($level)) {
            return $this->logger ?: null;
        } else {
            $this->logger->log($level, $message, $context);
        }
        return null;
    }

    /**
     * @inheritDoc
     */
    public function messages($key): ?MessagesBagContract
    {
        return $this->summary("items.{$key}.data.messages", null);
    }

    /**
     * @inheritDoc
     */
    public function params($key = null, $default = null)
    {
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
    public function reader(): ?ReaderContract
    {
        return $this->reader;
    }

    /**
     * @inheritDoc
     */
    public function setAfter(callable $func): ImportRecordsContract
    {
        array_push($this->callable['after'], $func);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAfterItem(callable $func): ImportRecordsContract
    {
        array_push($this->callable['after_item'], $func);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBefore(callable $func): ImportRecordsContract
    {
        array_push($this->callable['before'], $func);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBeforeItem(callable $func): ImportRecordsContract
    {
        array_push($this->callable['before_item'], $func);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLabels(LabelsBagContract $labels): ImportRecordsContract
    {
        $this->labels = $labels->parse();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLength(int $length): ImportRecordsContract
    {
        $this->length = $length > 0 ? $length : null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLogger(LoggerContract $logger): ImportRecordsContract
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOffset(int $offset): ImportRecordsContract
    {
        $this->offset = $offset > 0 ? $offset : 0;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setParams(array $params): ImportRecordsContract
    {
        $this->params = (new ParamsBag())->set($params);

        if ($labels = $this->params('labels')) {
            if ($labels instanceof LabelsBagContract) {
                $this->setLabels($labels);
            } elseif (is_array($labels)) {
                $this->setLabels(LabelsBag::createFromAttrs($labels));
            }
        }

        if ($path = $this->params('path')) {
            $this->fromPath((string)$path);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setReader(ReaderContract $reader): ImportRecordsContract
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function summary($key = null, $default = null)
    {
        if (is_string($key)) {
            return $this->summary->get($key, $default);
        } elseif (is_array($key)) {
            return $this->summary->set($key);
        } else {
            return $this->summary;
        }
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return ($items = $this->collect()->map(function (ImportRecordContract $item) {
            return $item->input()->all();
        })) ? $items->all() : [];
    }

    /**
     * @inheritDoc
     */
    public function walk($record, $key = null): ImportRecordContract
    {
        if ($record instanceof ImportRecordContract) {
            $record = clone $record;
        } elseif (($factory = $this->params->get('record')) && ($factory instanceof ImportRecordContract)) {
            $record = clone $factory->setInput($record);
        } else {
            $factory = $this->params->get('record');
            $input = $record;
            /** @var ImportRecordContract $record */
            $record = (class_exists($factory) ? new $factory() : new ImportRecord())->setInput($input);
        }

        return $this->items[$key] = $record->setRecords($this)->prepare();
    }
}