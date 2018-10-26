<?php

namespace tiFy\Plugins\Transaction\Contracts;

interface ImportItemWpPostInterface extends ImportItemInterface
{
    /**
     * Récupération de la liste des termes d'une taxonomie à taiter en sortie.
     * {@internal Renvoie la liste complète si la clé d'indexe n'est pas définie.}
     *
     * @param null|string $taxonomy Nom de qualification de la taxonomie.
     * @param mixed $terms Liste des termes de retour par défaut.
     *
     * @return mixed
     */
    public function getOutputTax($taxonomy = null, $terms = []);

    /**
     * Insertion de la liste des termes d'une taxonomie.
     *
     * @param string $taxonomy Nom de qualification de la taxonomie.
     * @param array $terms Liste des termes à insérer.
     * @param mixed $primary_id Valeur de clé primaire de l'élément.
     *
     * @return mixed
     */
    public function insertTax($taxonomy, $terms, $insert_id);

    /**
     * Evénement post-insertion des termes de taxonomies.
     *
     * @param array $taxonomies Liste des données de taxonomie à insérer.
     * @param mixed $primary_id Valeur de clé primaire de l'élément.
     *
     * @return void
     */
    public function insertTaxAfter($taxonomies = [], $primary_id = null);

    /**
     * Evénement pré-insertion des termes de taxonomies.
     *
     * @param array $taxonomies Liste des données de taxonomie à insérer.
     * @param mixed $primary_id Valeur de clé primaire de l'élément.
     *
     * @return void
     */
    public function insertTaxBefore($taxonomies = [], $primary_id = null);

    /**
     * Définition de la cartographie des taxonomies.
     *
     * @return array
     */
    public function mapTax();

    /**
     * Définition de la liste des termes d'une taxonomie à traiter.
     *
     * @param string $taxonomy Nom de qualification de la taxonomie.
     * @param mixed $raw_value Valeur brute de la donnée.
     *
     * @return mixed
     */
    public function outputSetTax($taxonomy, $raw_value = null);

    /**
     * Vérification de la liste des termes d'une taxonomie à traiter.
     *
     * @param string $taxonomy Nom de qualification de la taxonomie.
     * @param mixed $terms Liste des termes de la taxonomie.
     * @param mixed $post_id Valeur de la clé primaire de l'élément.
     *
     * @return mixed
     */
    public function outputCheckTax($taxonomy, $terms = null, $post_id = null);

    /**
     * Filtrage de la liste des termes d'une taxonomie à traiter.
     *
     * @param string $taxonomy Nom de qualification de la taxonomie.
     * @param mixed $terms Liste des termes de la taxonomie.
     * @param mixed $post_id Valeur de la clé primaire de l'élément.
     *
     * @return mixed
     */
    public function outputFilterTax($taxonomy, $terms = null, $post_id = null);
}