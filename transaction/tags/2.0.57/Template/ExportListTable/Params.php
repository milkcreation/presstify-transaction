<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ExportListTable;

use tiFy\Template\Templates\ListTable\Params as BaseParams;

class Params extends BaseParams
{
    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return array_merge(parent::defaults(), [
            'extras'       => ['export'],
        ]);
    }
}