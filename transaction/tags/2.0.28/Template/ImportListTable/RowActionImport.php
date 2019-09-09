<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Template\Templates\ListTable\RowAction as BaseRowAction;

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
                'data'    => [
                    $records->messages($item->getOffset())->fetch()
                ]
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
}