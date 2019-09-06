<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Contracts;

use tiFy\Plugins\Transaction\Contracts\ImportRecord;
use WP_Term;

interface ImportRecordWpTerm extends ImportRecord
{
    /**
     * Retrouve l'identifiant de qualification du terme.
     *
     * @return static
     */
    public function fetchTermId(): ImportRecordWpTerm;

    /**
     * Retrouve le nom de qualification de la taxonomie associée.
     *
     * @return static
     */
    public function fetchTaxonomy(): ImportRecordWpTerm;

    /**
     * Récupération du nom de qualification de la taxonomie associée.
     *
     * @return string
     */
    public function getTaxonomy();

    /**
     * Récupération de l'instance du terme Wordpress associé.
     *
     * @return WP_Term|null
     */
    public function getTerm(): ?WP_Term;

    /**
     * Enregistrement des métadonnées.
     *
     * @return static
     */
    public function saveMetas(): ImportRecordWpTerm;

    /**
     * Définition du nom de qualification de la taxonomie associée.
     *
     * @param string $taxonomy
     *
     * @return static
     */
    public function setTaxonomy(string $taxonomy): ImportRecordWpTerm;
}