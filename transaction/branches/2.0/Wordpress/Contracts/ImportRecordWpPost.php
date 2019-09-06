<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Contracts;

use tiFy\Plugins\Transaction\Contracts\ImportRecord;
use WP_Post;

interface ImportRecordWpPost extends ImportRecord
{
    /**
     * Rétrouve l'identifiant de qualification du post.
     *
     * @return static
     */
    public function fetchID(): ImportRecordWpPost;

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
    public function saveMetas(): ImportRecordWpPost;

    /**
     * Enregistrement des termes de taxonomies.
     *
     * @return static
     */
    public function saveTerms(): ImportRecordWpPost;
}