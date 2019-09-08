<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Template\Templates\ListTable\RowAction as BaseRowAction;
use tiFy\Template\Templates\ListTable\Contracts\RowAction as BaseRowActionContract;

class RowActionImport extends BaseRowAction
{
    /**
     * Instance du gabarit associé.
     * @var Factory
     */
    protected $factory;

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

    /**
     * @inheritDoc
     */
    public function httpController()
    {
        if ($item = $this->factory->builder()->getItem($this->factory->request()->input('id'))) {
            $records = $this->factory->records()->executeRecord($item->getOffset());

            return [
                'success' => true,
                'data'    => $records->messages($item->getOffset())->fetch()
            ];
        } else {
            return [
                'success' => false,
                'data'    => __('Impossible de récupérer l\'élément associé.', 'tify')
            ];
        }
    }

    /**
     * @inheritDoc
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function parse(): BaseRowActionContract
    {
        parent::parse();

        if ($this->factory->ajax()) {
            $this->set('attrs.data-control', 'list-table.row-action.import');
        }

        return $this;
    }
}