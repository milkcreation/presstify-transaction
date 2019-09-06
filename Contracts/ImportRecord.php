<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Contracts;

use tiFy\Contracts\Support\ParamsBag;

interface ImportRecord
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
     * @return static
     */
    public function execute(): ImportRecord;

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
    public function getResults(): array;

    /**
     * Définition/Récupération des données d'entrées.
     *
     * @return mixed|ParamsBag
     */
    public function input();

    /**
     * Définition/Récupération des messages de notification.
     *
     * @return mixed|ParamsBag
     */
    public function messages();

    /**
     * Initialisation d'une instance de la classe.
     *
     * @return static
     */
    public function prepare(): ImportRecord;

    /**
     * Définition/Récupération des messages de notification.
     *
     * @return mixed|ParamsBag
     */
    public function output();

    /**
     * Récupération de l'instance du gestionnaire d'import.
     *
     * @return ImportRecords
     */
    public function records(): ImportRecords;

    /**
     * Enregistrement des données d'import.
     *
     * @return static
     */
    public function save(): ImportRecord;

    /**
     * Définition de l'indice de traitement de l'élément.
     *
     * @param int $index
     *
     * @return static
     */
    public function setIndex(int $index): ImportRecord;

    /**
     * Définition de la liste des données d'entrée.
     *
     * @param iterable $input
     *
     * @return static
     */
    public function setInput(iterable $input): ImportRecord;

    /**
     * Définition de l'instance du gestionnaire d'import.
     *
     * @param ImportRecords $records
     *
     * @return static
     */
    public function setRecords(ImportRecords $records): ImportRecord;

    /**
     * Définition de la valeur de la clé primaire de l'élément.
     *
     * @param mixed $primary Valeur de la clé primaire.
     *
     * @return static
     */
    public function setPrimary($primary): ImportRecord;

    /**
     * Définition de la statut de réussite de la tâche.
     *
     * @param boolean $success Valeur de réussite
     *
     * @return static
     */
    public function setSuccess($success = true): ImportRecord;
}