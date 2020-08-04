<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Plugins\Parser\Template\FileListTable\Factory as BaseFactory;
use tiFy\Plugins\Transaction\Template\ImportListTable\Contracts\{
    Item as ItemContract,
    Factory as FactoryContract
};
use tiFy\Plugins\Transaction\Contracts\ImportRecorder;
use tiFy\Template\Templates\ListTable\Contracts\Item as BaseItem;

class Factory extends BaseFactory implements FactoryContract
{
    /**
     * Liste des fournisseurs de services.
     * @var string[]
     */
    protected $serviceProviders = [
        ServiceProvider::class,
    ];

    /**
     * {@inheritDoc}
     *
     * @return ItemContract
     */
    public function item(): ?BaseItem
    {
        return parent::item();
    }

    /**
     * @inheritDoc
     */
    public function recorder(): ImportRecorder
    {
        return $this->resolve('recorder');
    }
}