<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Plugins\Parser\Template\FileListTable\FileListTable as BaseListTable;

class ImportListTable extends BaseListTable
{
    /**
     * Liste des fournisseurs de services.
     * @var string[]
     */
    protected $serviceProviders = [
        ImportListTableServiceProvider::class,
    ];
}