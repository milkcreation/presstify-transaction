<?php

namespace tiFy\Plugins\Transaction\Contracts;

interface ImportItemWpTaxonomyInterface extends ImportItemInterface
{
    /**
     * Récupération du nom de qualification de la taxonomie à traiter.
     *
     * @return string
     */
    public function getTaxonomy();
}