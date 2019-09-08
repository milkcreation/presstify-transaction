<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use Traversable;
use tiFy\Plugins\Transaction\Contracts\{ImportRecord as ImportRecordContract, ImportRecords};
use tiFy\Support\{MessagesBag, ParamsBag};

class ImportRecord implements ImportRecordContract
{
    /**
     * Elements existant associé au données d'import.
     * {@internal Déclenche la mise à jour si définie, sinon crée un nouvel élément.}
     * @var mixed
     */
    protected $exists;

    /**
     * Indice de traitement de l'import de l'élément.
     * @var int
     */
    protected $index;

    /**
     * Instance des données d'entrée.
     * @var ParamsBag
     */
    protected $input;

    /**
     * Instance du gestionnaire d'import.
     * @var ImportRecords
     */
    protected $records;

    /**
     * Instance de la classe de traitement des messages de notification.
     * @var MessagesBag
     */
    protected $messages;

    /**
     * Instance des données de sortie.
     * @var ParamsBag
     */
    protected $output;

    /**
     * Indicateur de préparation.
     * @var boolean
     */
    protected $prepared = false;

    /**
     * Indicateur de succès de la tâche.
     * @var boolean
     */
    protected $success = false;

    /**
     * CONSTRUCTEUR.
     *
     * @return void
     */
    public function __construct()
    {
        $this->messages = new MessagesBag();
        $this->output = new ParamsBag();

        $this->boot();
    }

    /**
     * @inheritDoc
     */
    public function boot(): void { }

    /**
     * @inheritDoc
     */
    public function execute(): ImportRecordContract
    {
        $this->save();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function exists()
    {
        return $this->exists ?: null;
    }

    /**
     * @inheritDoc
     */
    public function fetchExists(): ImportRecordContract
    {
        if (is_null($this->exists)) {
            $this->exists = false;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getResults(): array
    {
        return [
            'success' => $this->getSuccess(),
            'data'    => [
                'messages'  => $this->messages(),
                'count'     => [
                    'error'   => $this->messages()->count(MessagesBag::ERROR),
                    'info'    => $this->messages()->count(MessagesBag::INFO),
                    'success' => $this->messages()->count(MessagesBag::NOTICE),
                    'warning' => $this->messages()->count(MessagesBag::WARNING),
                ],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSuccess(): bool
    {
        return !!$this->success;
    }

    /**
     * @inheritDoc
     */
    public function input($key = null, $default = null)
    {
        if (is_string($key)) {
            return $this->input->get($key, $default);
        } elseif (is_array($key)) {
            return $this->input->set($key);
        } else {
            return $this->input;
        }
    }

    /**
     * @inheritDoc
     */
    public function messages(): MessagesBag
    {
        return $this->messages;
    }

    /**
     * @inheritDoc
     */
    public function output($key = null, $default = null)
    {
        if (is_string($key)) {
            return $this->output->get($key, $default);
        } elseif (is_array($key)) {
            return $this->output->set($key);
        } else {
            return $this->output;
        }
    }

    /**
     * @inheritDoc
     */
    public function prepare(): ImportRecordContract
    {
        if (!$this->prepared) {
            $this->fetchExists();

            $this->prepared = true;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function records(): ImportRecords
    {
        return $this->records;
    }

    /**
     * @inheritDoc
     */
    public function save(): ImportRecordContract
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setExists($exists = null): ImportRecordContract
    {
        $this->exists = $exists;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setIndex(int $index): ImportRecordContract
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setInput(iterable $input): ImportRecordContract
    {
        if ($input instanceof Traversable) {
            $input = iterator_to_array($input);
        }

        $this->input = (new ParamsBag())->set($input);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setRecords(ImportRecords $records): ImportRecordContract
    {
        $this->records = $records;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSuccess($success = true): ImportRecordContract
    {
        $this->success = $success;

        return $this;
    }
}