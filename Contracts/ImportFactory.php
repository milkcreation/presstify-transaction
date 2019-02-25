<?php

namespace tiFy\Plugins\Transaction\Contracts;

use tiFy\Contracts\Kernel\Notices;
use tiFy\Contracts\Kernel\ParamsBag;

interface ImportFactory extends ParamsBag
{
    /**
     * Evénement déclenché post-insertion.
     *
     * @param mixed $primary_id Valeur de la clé primaire de l'élément.
     *
     * @return void
     */
    public function after($primary_id = null);

    /**
     * Evénement déclenché pré-insertion.
     *
     * @param mixed $primary_id Valeur de la clé primaire de l'élément.
     *
     * @return void
     */
    public function before($primary_id = null);

    /**
     * Initialisation du controleur.
     *
     * @return void
     */
    public function boot();

    /**
     * Récupération de valeur de donnée d'entrée.
     * {@internal Renvoie la liste complète si la clé d'indexe n'est pas définie.}
     *
     * @param null|string $key Clé d'indexe de la valeur à récupérer. Syntaxe à point permise.
     * @param mixed $default Valeur de retour par défaut.
     *
     * @return mixed
     */
    public function getInput($key = null, $default = null);

    /**
     * Récupération de la cartographie d'un type de données à traiter.
     *
     * @param string $type Type des données cartographiées. data|meta.
     *
     * @return array
     */
    public function getMap($type);

    /**
     * Récupération de valeur de donnée principale de sortie.
     * {@internal Renvoie la liste complète si la clé d'indexe n'est pas définie.}
     *
     * @param null|string $key Clé d'indexe de la valeur à récupérer. Syntaxe à point permise.
     * @param mixed $default Valeur de retour par défaut.
     *
     * @return mixed
     */
    public function getOutputData($key = null, $default = null);

    /**
     * Récupération de valeur de métadonnée de sortie.
     * {@internal Renvoie la liste complète si la clé d'indexe n'est pas définie.}
     *
     * @param null|string $meta_key Clé d'indexe de la valeur à récupérer. Syntaxe à point permise.
     * @param mixed $default Valeur de retour par défaut.
     *
     * @return mixed
     */
    public function getOutputMeta($meta_key = null, $default = null);

    /**
     * Récupération de la valeur de clé primaire de l'élément.
     *
     * @return mixed
     */
    public function getPrimaryId();

    /**
     * Récupération du résultat de traitement.
     *
     * @return array
     */
    public function getResults();

    /**
     * Récupération du message de notification en cas de réussite de traitement de l'import.
     *
     * @param mixed $primary_id Valeur de clé primaire de l'élément enregistré.
     *
     * @return string
     */
    public function getSuccessMessage($primary_id = null);

    /**
     * Récupération de la liste des types de données à traiter.
     *
     * @return array
     */
    public function getTypes();

    /**
     * Insertion des données principales.
     *
     * @param array $datas Liste des données à insérer.
     * @param mixed $primary_id Valeur de clé primaire de l'élément.
     *
     * @return void
     */
    public function insertData($datas = [], $primary_id = null);

    /**
     * Evénement post-insertion des données principales.
     *
     * @param array $datas Liste des données à insérer.
     * @param mixed $primary_id Valeur de clé primaire de l'élément.
     *
     * @return void
     */
    public function insertDataAfter($datas = [], $primary_id = null);

    /**
     * Evénement pré-insertion des données principales.
     *
     * @param array $datas Liste des données à insérer.
     * @param mixed $primary_id Valeur de clé primaire de l'élément.
     *
     * @return void
     */
    public function insertDataBefore($datas = [], $primary_id = null);

    /**
     * Insertion d'une métadonnée.
     *
     * @param string $meta_key Clé d'indexe de la metadonnée.
     * @param mixed $meta_value Valeur de la metadonnée à insérer.
     * @param mixed $primary_id Valeur de clé primaire de l'élément.
     *
     * @return mixed
     */
    public function insertMeta($meta_key, $meta_value, $primary_id = null);

    /**
     * Evénement post-insertion des metadonnées.
     *
     * @param array $metas Liste des metadonnées à insérer.
     * @param mixed $primary_id Valeur de clé primaire de l'élément.
     *
     * @return void
     */
    public function insertMetaAfter($metas = [], $primary_id = null);

    /**
     * Evénement pré-insertion des metadonnées.
     *
     * @param array $metas Liste des metadonnées à insérer.
     * @param mixed $primary_id Valeur de clé primaire de l'élément.
     *
     * @return void
     */
    public function insertMetaBefore($metas = [], $primary_id = null);

    /**
     * Vérification du succès de la tâche.
     *
     * @return boolean
     */
    public function isSuccessfully();

    /**
     * Définition de la cartographie des données principales.
     *
     * @return array
     */
    public function mapData();

    /**
     * Définition de la cartographie des metadonnées.
     *
     * @return array
     */
    public function mapMeta();

    /**
     * Récupération de l'instance de la classe de traitement des messages de notification.
     *
     * @return Notices
     */
    public function notices();

    /**
     * Vérification de l'arrêt de la tâche.
     *
     * @return boolean
     */
    public function onBreak();

    /**
     * Vérification d'une donnée de sortie à traiter.
     *
     * @param string $key Clé d'indexe de la donnée.
     * @param mixed $value Valeur de la donnée.
     * @param mixed $primary_id Valeur de la clé primaire de l'élément.
     *
     * @return mixed
     */
    public function outputCheckData($key, $value = null, $primary_id = null);

    /**
     * Vérification d'une métadonnée de sortie à traiter.
     *
     * @param string $meta_key Clé d'indexe de la donnée.
     * @param mixed $meta_value Valeur de la donnée.
     * @param mixed $primary_id Valeur de la clé primaire de l'élément.
     *
     * @return mixed
     */
    public function outputCheckMeta($meta_key, $meta_value = null, $primary_id = null);

    /**
     * Filtrage d'une donnée de sortie à traiter.
     *
     * @param string $key Clé d'indexe de la donnée.
     * @param mixed $value Valeur de la donnée.
     * @param mixed $primary_id Valeur de la clé primaire de l'élément.
     *
     * @return mixed
     */
    public function outputFilterData($key, $value = null, $primary_id = null);

    /**
     * Définition d'une métadonnée de sortie à traiter.
     *
     * @param string $meta_key Clé d'indexe de la donnée.
     * @param mixed $meta_value Valeur de la donnée.
     * @param mixed $primary_id Valeur de la clé primaire de l'élément.
     *
     * @return mixed
     */
    public function outputFilterMeta($meta_key, $meta_value = null, $primary_id = null);

    /**
     * Définition d'une donnée de sortie à traiter.
     *
     * @param string $key Clé d'indexe de la donnée.
     * @param mixed $raw_value Valeur brute de la donnée.
     *
     * @return mixed
     */
    public function outputSetData($key, $raw_value = null);

    /**
     * Définition d'une métadonnée de sortie à traiter.
     *
     * @param string $meta_key Clé d'indexe de la donnée.
     * @param mixed $raw_meta_value Valeur brute de la donnée.
     *
     * @return mixed
     */
    public function outputSetMeta($meta_key, $raw_meta_value = null);

    /**
     * Pré-traitement des données d'entrées brutes.
     * {@internal Permet de convertir des données d'entrées de type iterateur au format tableau requis par la classe.}
     *
     * @param mixed $input Liste des données d'entrées brutes.
     *
     * @return array
     */
    public function parseInput($input);

    /**
     * {@inheritdoc}
     */
    public function proceed();

    /**
     * Définition de l'interruption de la tâche.
     *
     * @return $this
     */
    public function setOnBreak();

    /**
     * Définition de la valeur de la clé primaire de l'élément.
     *
     * @param mixed $primary_id Valeur de la clé primaire.
     *
     * @return $this
     */
    public function setPrimaryId($primary_id);

    /**
     * Définition de la statut de réussite de la tâche.
     *
     * @param boolean $success Valeur de réussite
     *
     * @return $this
     */
    public function setSuccess($success = true);
}