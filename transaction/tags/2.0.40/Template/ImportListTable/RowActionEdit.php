<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Template\Templates\ListTable\RowActionEdit as BaseRowActionEdit;

class RowActionEdit extends BaseRowActionEdit
{
    /**
     * Instance du gabarit associé.
     * @var Factory
     */
    protected $factory;

    /**
     * @inheritDoc
     */
    public function isAvailable(): bool
    {
        return !!$this->factory->item()->exists();
    }
}