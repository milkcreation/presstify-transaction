<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable\Contracts;

use Exception;
use tiFy\Template\Templates\ListTable\Contracts\Actions as BaseActions;

interface Actions extends BaseActions
{
    /**
     * Import d'éléments.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function doImport();
}