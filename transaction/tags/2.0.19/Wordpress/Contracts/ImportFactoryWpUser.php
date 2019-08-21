<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Contracts;

use tiFy\Plugins\Transaction\Contracts\ImportFactory;
use WP_User;

interface ImportFactoryWpUser extends ImportFactory
{
    /**
     * Retrouve l'identifiant de qualification du site d'affectation.
     *
     * @return static
     */
    public function fetchBlogId(): ImportFactoryWpUser;

    /**
     * Retrouve l'identifiant de qualification de l'utilisateur.
     *
     * @return static
     */
    public function fetchID(): ImportFactoryWpUser;

    /**
     * Retrouve l'intitulé de qualification du role associée.
     *
     * @return static
     */
    public function fetchRole(): ImportFactoryWpUser;

    /**
     * Retrouve le mot de passe associé.
     * {@internal Hachage si nécessaire.}
     *
     * @return static
     */
    public function fetchUserPass(): ImportFactoryWpUser;

    /**
     * Retrouve l'identifiant de qualification du site d'affectation.
     *
     * @return int
     */
    public function getBlogId(): int;

    /**
     * Récupération du nom de qualification de la taxonomie associée.
     *
     * @return string
     */
    public function getRole();

    /**
     * Récupération de l'instance de l'utilisateur Wordpress associé.
     *
     * @return WP_User|null
     */
    public function getUser(): ?WP_User;

    /**
     * Vérification d'existance du rôle associé.
     *
     * @return bool
     */
    public function isRole(): bool;

    /**
     * Enregistrement des métadonnées.
     *
     * @return static
     */
    public function saveMetas(): ImportFactoryWpUser;

    /**
     * Enregistrement des options.
     *
     * @return static
     */
    public function saveOptions(): ImportFactoryWpUser;

    /**
     * Définition de l'identifiant de qualification du site d'affection.
     *
     * @param int $blog_id
     *
     * @return static
     */
    public function setBlogId(int $blog_id): ImportFactoryWpUser;

    /**
     * Définition du nom de qualification du rôle associé.
     *
     * @param string $role
     *
     * @return static
     */
    public function setRole(string $role): ImportFactoryWpUser;
}