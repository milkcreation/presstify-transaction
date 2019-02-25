<?php

namespace tiFy\Plugins\Transaction\Wp\Contracts;

use tiFy\Plugins\Transaction\Contracts\ImportFactory;

interface ImportFactoryTerm extends ImportFactory
{
    /**
     * Récupération du nom de qualification de la taxonomie à traiter.
     *
     * @return string
     */
    public function getTaxonomy();
}