<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Contracts;

interface ImportManager
{
    /**
     * Création ou mise à jour d'une relation d'import.
     *
     * @param string $object_type Type de relation.
     * @param int $object_id Identifiant de qualification de l'élément.
     * @param string|int $relation Identifiant relationnel de l'élément.
     * @param array $data Liste des données complémentaires
     *
     * @return bool
     */
    public function add(string $object_type, int $object_id, $relation, array $data = []): bool;

    /**
     * Création ou mise à jour d'une relation d'import d'un post Wordpress.
     *
     * @param int $object_id Identifiant de qualification de l'élément.
     * @param string|int $relation Identifiant relationnel de l'élément.
     * @param array $data Liste des données complémentaires
     *
     * @return bool
     */
    public function addWpPost(int $object_id, $relation, array $data = []): bool;

    /**
     * Création ou mise à jour d'une relation d'import d'un terme de taxonomie Wordpress.
     *
     * @param int $object_id Identifiant de qualification de l'élément.
     * @param string|int $relation Identifiant relationnel de l'élément.
     * @param array $data Liste des données complémentaires
     *
     * @return bool
     */
    public function addWpTerm(int $object_id, $relation, array $data = []): bool;

    /**
     * Création ou mise à jour d'une relation d'import d'un utilisateur Wordpress.
     *
     * @param int $object_id Identifiant de qualification de l'élément.
     * @param string|int $relation Identifiant relationnel de l'élément.
     * @param array $data Liste des données complémentaires
     *
     * @return bool
     */
    public function addWpUser(int $object_id, $relation, array $data = []): bool;

    /**
     * Récupération d'un import existant basé sur le type et l'identifiant relationnel.
     *
     * @param string|int $relation Identifiant relationnel de l'élément.
     * @param string $object_type Type de relation.
     *
     * @return object|null
     */
    public function getFromRelation($relation, string $object_type): ?object;

    /**
     * Récupération d'un import existant basé sur le type et l'identifiant de qualification de l'élément.
     *
     * @param int $object_id Identifiant relationnel de l'élément.
     * @param string $object_type Type de relation.
     *
     * @return object|null
     */
    public function getFromObjectId(int $object_id, string $object_type): ?object;

    /**
     * Récupération d'un import existant basé sur un objet Wordpress.
     *
     * @param \WP_Post|\WP_Term|\WP_User|object $object Type de relation.
     *
     * @return object|null
     */
    public function getFromWpObject(object $object): ?object;

    /**
     * Récupération de l'identifiant de qualification d'un élément associé à le type et l'identifiant relationnel.
     *
     * @param string|int $relation Identifiant relationnel de l'élément.
     * @param string $object_type Type de relation.
     *
     * @return int
     */
    public function getObjectId($relation, string $object_type): int;

    /**
     * Récupération de l'identifiant de qualification d'un post Wordpress selon l'identifiant relationnel.
     *
     * @param string|int $relation Identifiant relationnel de l'élément.
     *
     * @return int
     */
    public function getWpPostId($relation): int;

    /**
     * Récupération de l'identifiant de qualification d'un terme de taxonomie Wordpress selon l'identifiant relationnel.
     *
     * @param string|int $relation Identifiant relationnel de l'élément.
     *
     * @return int
     */
    public function getWpTermId($relation): int;

    /**
     * Récupération de l'identifiant de qualification d'un utilisateur Wordpress selon l'identifiant relationnel.
     *
     * @param string|int $relation Identifiant relationnel de l'élément.
     *
     * @return int
     */
    public function getWpUserId($relation): int;

    /**
     * Récupération de l'instance d'une commande d'import.
     *
     * @param string $name Nom de qualification.
     *
     * @return ImportCommand|null
     */
    public function getCommand(string $name): ?ImportCommand;

    /**
     * Récupération de l'instance d'un jeu de commandes d'import.
     *
     * @param string $name Nom de qualification.
     *
     * @return ImportCommand|null
     */
    public function getCommandStack(string $name): ?ImportCommandStack;

    /**
     * Récupération de l'instance du gestionnaire d' enregistrements.
     *
     * @param string $name Nom de qualification.
     *
     * @return ImportCommand|null
     */
    public function getRecorder(string $name): ?ImportRecorder;

    /**
     * Déclaration d'une instance de commande d'import.
     *
     * @param string|null $name Nom de qualification.
     * @param ImportRecorder $recorder Instance du gestionnaire d' enregistrements.
     * @param array $params Liste des paramètres de configuration.
     *
     * @return ImportCommand|null
     */
    public function registerImportCommand(
        ?string $name = null,
        ?ImportRecorder $recorder = null,
        array $params = []
    ): ?ImportCommand;

    /**
     * Déclaration d'une instance de jeu de commandes d'import.
     *
     * @param string|null $name Nom de qualification.
     * @param string[] $stack Liste des noms de qualification des commandes associées.
     *
     * @return ImportCommandStack|null
     */
    public function registerCommandStack(?string $name = null, array $stack = []): ?ImportCommandStack;

    /**
     * Déclaration d'une instance de gestionnaire d'enregistrements.
     *
     * @param string $name Nom de qualification.
     * @param array $params Liste des paramètres de configuration.
     *
     * @return ImportCommand|null
     */
    public function registerRecorder(string $name, array $params = []): ?ImportRecorder;

    /**
     * Définition du gestionnaire de transaction.
     *
     * @param Transaction $transaction Instance du gestionnaire de transaction.
     *
     * @return static
     */
    public function setTransaction(Transaction $transaction): ImportManager;
}