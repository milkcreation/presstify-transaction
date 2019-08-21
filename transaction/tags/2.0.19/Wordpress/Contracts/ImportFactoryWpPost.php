<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Contracts;

use tiFy\Plugins\Transaction\Contracts\ImportFactory;
use WP_Post;

interface ImportFactoryWpPost extends ImportFactory
{
    /**
     * Rétrouve l'identifiant de qualification du post.
     *
     * @return static
     */
    public function fetchID(): ImportFactoryWpPost;

    /**
     * Récupération de l'instance du post Wordpress associé.
     *
     * @return WP_Post|null
     */
    public function getPost(): ?WP_Post;

    /**
     * Enregistrement des métadonnées.
     *
     * @return static
     */
    public function saveMetas(): ImportFactoryWpPost;

    /**
     * Enregistrement des termes de taxonomies.
     *
     * @return static
     */
    public function saveTerms(): ImportFactoryWpPost;
}