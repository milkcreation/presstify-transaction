<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use Traversable;
use tiFy\Plugins\Transaction\Contracts\{ImportRecord as ImportRecordContract, ImportRecorder};
use tiFy\Support\{MessagesBag, ParamsBag};

class ImportRecord implements ImportRecordContract
{
    /**
     * Indicateur d'initialisation.
     * @var bool
     */
    protected $built = false;

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
     * Instance du gestionnaire d'enregistrement.
     * @var ImportRecorder
     */
    protected $recorder;

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
     * @var bool
     */
    protected $prepared = false;

    /**
     * Indicateur de succès de la tâche.
     * @var bool
     */
    protected $success = false;

    /**
     * @inheritDoc
     */
    public function build(): ImportRecordContract
    {
        if (!$this->built) {
            $this->messages = new MessagesBag();
            $this->output = new ParamsBag();

            $this->built = true;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function execute(): ImportRecordContract
    {
        $this->prepare()->save();

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
    public function recorder(): ImportRecorder
    {
        return $this->recorder;
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
    public function saveInfos(): ImportRecordContract
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
    public function setRecorder(ImportRecorder $recorder): ImportRecordContract
    {
        $this->recorder = $recorder;

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