<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable\Contracts;

use tiFy\Support\DateTime;
use tiFy\Template\Templates\ListTable\Contracts\Item as BaseItem;

interface Item extends BaseItem
{
    /**
     * Récupération de l'élément existant.
     *
     * @return mixed
     */
    public function exists();

    /**
     * Récupération de la date d'import.
     *
     * @return DateTime|null
     */
    public function importDate(): ?DateTime;
}