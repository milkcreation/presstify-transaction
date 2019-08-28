<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use Exception;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface as Logger;
use tiFy\Plugins\Parser\{
    Contracts\Reader as ReaderContract, Reader
};
use tiFy\Plugins\Transaction\{
    Contracts\ImportFactory as ImportFactoryContract,
    Contracts\ImportManager as ImportManagerContract
};
use tiFy\Contracts\Support\{LabelsBag as LabelsBagContract};
use tiFy\Support\{DateTime, LabelsBag, MessagesBag, ParamsBag};
use Traversable;

class ImportManager implements ImportManagerContract
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
     * Indice de l'enregistrement de démarrage.
     * @var int
     */
    protected $offset = 0;

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
     * @var Logger|false|null
     */
    protected $logger;

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
     * Liste des éléments du fichier.
     * @var ImportFactoryContract[]
     */
    protected $records = [];

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
     */
    public function __construct($params = [])
    {
        $this
            ->setLabels(LabelsBag::createFromAttrs([]))
            ->setParams($params);
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public static function createFromPath(string $path, $params = [], $asserts = true): ImportManagerContract
    {
        try {
            return self::createFromReader(Reader::createFromPath($path), $params);
        } catch (Exception $e) {
            if ($asserts) {
                throw $e;
            } else {
                return new static($params);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function createFromReader(ReaderContract $reader, $params = []): ImportManagerContract
    {
        return (new static($params))->setReader($reader);
    }

    /**
     * @inheritDoc
     */
    public function callAfter(): ImportManagerContract
    {
        foreach($this->callable['after'] as $callable) {
            $callable($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function callAfterItem(ImportFactoryContract $item, $key): ImportManagerContract
    {
        foreach($this->callable['after_item'] as $callable) {
            $callable($this, $item, $key);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function callBefore(): ImportManagerContract
    {
        foreach($this->callable['before'] as $callable) {
            $callable($this);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function callBeforeItem(ImportFactoryContract $item, $key): ImportManagerContract
    {
        foreach($this->callable['before_item'] as $callable) {
            $callable($this, $item, $key);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function execute(): ImportManagerContract
    {
        $this->summary = new ParamsBag();

        $start = time() + (new DateTime())->getOffset();

        if ($this->reader) {
            $this->reader->setOffset($this->getOffset())->setPerPage($this->getLength())->fetch();
            $this->setRecord($this->reader->all());
        }

        $items = new Collection($this->getRecords());
        $count = $items->count();

        $this->summary([
            'index' => 0,
            'start' => $start,
            'count' => $count,
        ]);

        $this->callBefore();

        $this->logger(
            'info',
            sprintf(__('-------- Démarrage de l\'import des %s --------', 'tify'), $this->labels()->getPlural()),
            $this->summary()->all()
        );

        foreach ($items as $key => $item) {
            $this->executeItem($item, $key);
        }

        $end = time() + (new DateTime())->getOffset();
        $this->summary([
            'end'      => $end,
            'duration' => $end - $start,
        ]);

        $this->callAfter();

        $this->logger(
            'info',
            sprintf(__('-------- Fin de l\'import des %s --------', 'tify'), $this->labels()->getPlural()),
            $this->summary()->all()
        );

        $this->summary->clear();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function executeItem(ImportFactoryContract $item, $key): ImportManagerContract
    {
        $this->callBeforeItem($item, $key);

        $this->summary(["items.{$key}" => $item->setIndex((int)$this->summary('index', 0))->execute()->getResults()]);

        $this->summary(['index' => $this->summary('index', 0) + 1]);

        if (!$this->summary("items.{$key}.success")) {
            $this->summary(['failed' => $this->summary('failed', 0) + 1]);
        } else {
            $this->summary(['success' => $this->summary('success', 0) + 1]);
        }

        $this->callAfterItem($item, $key);

        $messages = $this->summary("items.{$key}.data.messages", null);

        if ($messages instanceof MessagesBag) {
            foreach ($messages->fetch() as $level => $item) {
                $this->logger($item['level'], $item['message'], $item['data']);
            }
        }

        return $this;
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
    public function getLength(): ?int
    {
        return $this->length;
    }

    /**
     * @inheritDoc
     */
    public function getRecords(): array
    {
        return $this->records;
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
    public function logger($level = null, string $message = '', array $context = []): ?Logger
    {
        if (is_null($this->logger)) {
            if ($logger = $this->params('logger', true)) {
                if (!$logger instanceof Logger) {
                    $logger = app('log');
                }
                $this->setLogger($logger);
            } else {
                return null;
            }
        }

        if(is_null($level)) {
            return $this->logger;
        } else {
            $this->logger->log($level, $message, $context);
        }
        return null;
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
    public function setAfter(callable $func): ImportManagerContract
    {
        array_push($this->callable['after'], $func);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setAfterItem(callable $func): ImportManagerContract
    {
        array_push($this->callable['after_item'], $func);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBefore(callable $func): ImportManagerContract
    {
        array_push($this->callable['before'], $func);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setBeforeItem(callable $func): ImportManagerContract
    {
        array_push($this->callable['before_item'], $func);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLabels(LabelsBagContract $labels): ImportManagerContract
    {
        $this->labels = $labels->parse();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLength(int $length): ImportManagerContract
    {
        $this->length = $length > 0 ? $length : null;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setLogger(Logger $logger): ImportManagerContract
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setOffset(int $offset): ImportManagerContract
    {
        $this->offset = $offset > 0 ? $offset : 0;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setParams(array $params): ImportManagerContract
    {
        $this->params = (new ParamsBag())->set($params);

        if ($labels = $this->params('labels')) {
            if ($labels instanceof LabelsBagContract) {
                $this->setLabels($labels);
            } elseif(is_array($labels)) {
                $this->setLabels(LabelsBag::createFromAttrs($labels));
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setReader(ReaderContract $reader): ImportManagerContract
    {
        $this->reader = $reader;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRecord($key, $value = null): ImportManagerContract
    {
        if (is_array($key)) {
            $keys = $key;
        } elseif ($key instanceof Traversable) {
            $keys = iterator_to_array($key);
        } else {
            $keys = [$key => $value];
        }

        array_walk($keys, [$this, 'walkRecord']);

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
    public function walkRecord($record, $key = null): ImportFactoryContract
    {
        if ($record instanceof ImportFactoryContract) {
            $record = clone $record;
        } elseif (($factory = $this->params->get('factory')) && ($factory instanceof ImportFactoryContract)) {
            $record = clone $factory->setInput($record);
        } else {
            $factory = $this->params->get('factory');
            $input = $record;
            /** @var ImportFactoryContract $record */
            $record = (class_exists($factory) ? new $factory() : new ImportFactory())->setInput($input);
        }

        return $this->records[$key] = $record->setManager($this)->prepare();
    }
}