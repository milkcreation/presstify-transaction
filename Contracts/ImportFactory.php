<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Contracts;

use tiFy\Contracts\Support\ParamsBag;

interface ImportFactory
{
    /**
     * Initialisation du controleur.
     *
     * @return void
     */
    public function boot(): void;

    /**
     * Execution de l'import des éléments.
     *
     * @return array
     */
    public function execute(): array;

    /**
     * Récupération de la valeur de clé primaire de l'élément.
     *
     * @return mixed
     */
    public function getPrimary();

    /**
     * Récupération du résultat de traitement.
     *
     * @return array
     */
    public function getResults();

    /**
     * Définition/Récupération des données d'entrées.
     *
     * @return mixed|ParamsBag
     */
    public function input();

    /**
     * Récupération de l'instance du gestionnaire d'import.
     *
     * @return ImportManager
     */
    public function manager();

    /**
     * Définition/Récupération des messages de notification.
     *
     * @return mixed|ParamsBag
     */
    public function messages();

    /**
     * Evénement déclenché au démarrage du traitement.
     *
     * @return void
     */
    public function onStart(): void;

    /**
     * Evénement déclenché à l'issue du traitement.
     *
     * @return void
     */
    public function onEnd(): void;

    /**
     * Définition/Récupération des messages de notification.
     *
     * @return mixed|ParamsBag
     */
    public function output();

    /**
     * Définition de la valeur de la clé primaire de l'élément.
     *
     * @param mixed $primary Valeur de la clé primaire.
     *
     * @return $this
     */
    public function setPrimary($primary);

    /**
     * Définition de la statut de réussite de la tâche.
     *
     * @param boolean $success Valeur de réussite
     *
     * @return $this
     */
    public function setSuccess($success = true);
}