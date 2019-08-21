<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use tiFy\Plugins\Transaction\Contracts\{ImportFactory as ImportFactoryContract, ImportManager};
use tiFy\Support\{MessagesBag, ParamsBag};

class ImportFactory implements ImportFactoryContract
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
     * @var ImportManager
     */
    protected $manager;

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
     * CONSTRUCTEUR.
     *
     * @param array $input Liste des données d'entrées.
     * @param ImportManager $manager Instance du gestionnaire d'import associé.
     *
     * @return void
     */
    public function __construct(array $input, ImportManager $manager)
    {
        $this->manager = $manager;
        $this->input = ParamsBag::createFromAttrs($input);
        $this->output = new ParamsBag();
        $this->messages = new MessagesBag();

        $this->boot();
    }

    /**
     * @inheritDoc
     */
    public function boot(): void { }

    /**
     * @inheritDoc
     */
    public function execute(): ImportFactoryContract
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
    public function setPrimary($primary): ImportFactoryContract
    {
        $this->primary = $primary;

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
    public function setSuccess($success = true): ImportFactoryContract
    {
        $this->success = $success;

        return $this;
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
    public function manager(): ImportManager
    {
        return $this->manager;
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
    public function save(): ImportFactoryContract
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setIndex(int $index): ImportFactoryContract
    {
        $this->index = $index;

        return $this;
    }
}