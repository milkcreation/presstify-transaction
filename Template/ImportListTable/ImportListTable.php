<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Contracts\Template\FactoryBuilder;
use tiFy\Template\Templates\ListTable\Contracts\Builder;
use tiFy\Template\Templates\ListTable\Contracts\DbBuilder;
use tiFy\Template\Templates\ListTable\ListTable as BaseListTable;
use tiFy\Plugins\Transaction\Template\ImportListTable\Contracts\FileBuilder;

class ImportListTable extends BaseListTable
{
    /**
     * Liste des fournisseurs de services.
     * @var string[]
     */
    protected $serviceProviders = [
        ImportListTableServiceProvider::class,
    ];

    /**
     * {@inheritDoc}
     *
     * @return Builder|DbBuilder|FileBuilder
     */
    public function builder(): FactoryBuilder
    {
        return parent::builder();
    }
}