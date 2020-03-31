<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Support\DateTime;
use tiFy\Template\Templates\ListTable\Item as BaseItem;
use tiFy\Plugins\Transaction\Template\ImportListTable\Contracts\Item as ItemContract;

class Item extends BaseItem implements ItemContract
{
    /**
     * Instance du gabarit associé.
     * @var Factory
     */
    protected $factory;

    /**
     * @inheritDoc
     */
    public function exists()
    {
        return ($record = $this->factory->recorder()->get($this->getOffset()))
            ? $record->prepare()->exists()
            : null;
    }

    /**
     * @inheritDoc
     */
    public function importDate(): ?DateTime
    {
        return null;
    }
}