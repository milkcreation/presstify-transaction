<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ExportListTable\Contracts;

use Exception;
use tiFy\Template\Templates\ListTable\Contracts\Actions as BaseActions;

interface Actions extends BaseActions
{
    /**
     * Export d'éléments.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function executeExport();
}