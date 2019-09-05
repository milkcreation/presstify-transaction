<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Template\Templates\ListTable\RowAction;

class RowActionImport extends RowAction
{
    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return array_merge(parent::defaults(), [
            'attrs'   => [
                'title' => sprintf(__('Modification %s', 'tify'), $this->factory->label()->singularDefinite(true)),
            ],
            'content' => __('Importer', 'tify'),
        ]);
    }
}