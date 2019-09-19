<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable\Contracts;

use tiFy\Plugins\Parser\Template\FileListTable\Contracts\Factory as BaseFactory;
use tiFy\Plugins\Transaction\Contracts\ImportRecords;
use tiFy\Template\Templates\ListTable\Contracts\Item as BaseItem;

interface Factory extends BaseFactory
{
    /**
     * {@inheritDoc}
     *
     * @return Item
     */
    public function item(): ?BaseItem;
    /**
     * @inheritDoc
     */
    public function records(): ImportRecords;
}