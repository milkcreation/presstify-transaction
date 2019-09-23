<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Contracts;

use tiFy\Plugins\Transaction\Contracts\ImportRecord;
use WP_Post;

interface ImportWpPost extends ImportRecord
{
    /**
     * @inheritDoc
     */
    public function exists(): ?WP_Post;

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
    public function saveMetas(): ImportWpPost;

    /**
     * Enregistrement des termes de taxonomies.
     *
     * @return static
     */
    public function saveTerms(): ImportWpPost;
}