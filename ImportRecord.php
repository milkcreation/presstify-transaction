<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use Traversable;
use tiFy\Plugins\Transaction\Contracts\{ImportRecord as ImportRecordContract, ImportRecords};
use tiFy\Support\{MessagesBag, ParamsBag};

class ImportRecord implements ImportRecordContract
{
    /**
     * Indice de traitement de l'import de l'élément
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
     * Valeur de clé primaire de l'élément.
     * {@internal Déclenche la mise à jour si définie, sinon crée un nouvel élément.}
     * @var mixed
     */
    protected $primary = null;

    /**
     * Indicateur de succès de la tâche.
     * @var boolean
     */
    protected $success = false;

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
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     * @inheritDoc
     */
    public function getResults(): array
    {
        return [
            'success' => $this->getSuccess(),
            'data'    => [
                'insert_id' => $this->getPrimary(),
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
        $this->messages = new MessagesBag();
        $this->output = new ParamsBag();

        $this->boot();

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
    public function setPrimary($primary): ImportRecordContract
    {
        $this->primary = $primary;

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