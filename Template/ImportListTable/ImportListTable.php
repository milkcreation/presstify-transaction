<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Plugins\Parser\Template\FileListTable\FileListTable as BaseListTable;
use tiFy\Plugins\Transaction\Template\ImportListTable\Contracts\{
    ImportListTable as ImportListTableContract
};
use tiFy\Plugins\Transaction\Contracts\ImportRecords;

class ImportListTable extends BaseListTable implements ImportListTableContract
{
    /**
     * Liste des fournisseurs de services.
     * @var string[]
     */
    protected $serviceProviders = [
        ImportListTableServiceProvider::class,
    ];

    /**
     * @inheritDoc
     */
    public function records(): ImportRecords
    {
        return $this->resolve('records');
    }
}