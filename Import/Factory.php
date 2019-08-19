<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Import;

use tiFy\Plugins\Transaction\Contracts\{
    ImportManager,
    ImportFactory as ImportFactoryContract
};
use tiFy\Support\{
    MessagesBag,
    ParamsBag
};

class Factory implements ImportFactoryContract
{
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
        $this->manager  = $manager;
        $this->input    = ParamsBag::createFromAttrs($input);
        $this->output   = new ParamsBag();
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
    public function execute(): array
    {
        return $this->getResults();
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
    public function getResults()
    {
        return [
            'success' => $this->getSuccess(),
            'data'    => [
                'insert_id' => $this->getPrimary(),
                'messages'  => $this->messages()->all(),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSuccess()
    {
        return ! ! $this->success;
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
    public function manager()
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
    public function OnEnd($primary_id = null): void { }

    /**
     * @inheritDoc
     */
    public function onStart($primary_id = null): void { }

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
    public function setPrimary($primary)
    {
        $this->primary = $primary;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSuccess($success = true)
    {
        $this->success = $success;

        return $this;
    }
}