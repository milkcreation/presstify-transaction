<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Template\Templates\ListTable\Column as BaseColumn;
use tiFy\Plugins\Transaction\Proxy\Transaction;

class ColumnImport extends BaseColumn
{
    /**
     * Instance du gabarit associé.
     * @var Factory
     */
    protected $factory;

    /**
     * @inheritDoc
     */
    public function canUseForPrimary(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return [
            'title' => __('Import', 'tify')
        ];
    }

    /**
     * @inheritDoc
     */
    public function value(): string
    {
        $this->set('item', $this->factory->item());

        return (string)view()
            ->setDirectory(Transaction::resourcesDir('/views/import-list-table'))
            ->make('col-import', $this->all());
    }
}