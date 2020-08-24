<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ExportListTable;

use tiFy\Template\Templates\ListTable\Factory as BaseFactory;

class Factory extends BaseFactory
{
    /**
     * Liste des fournisseurs de services.
     * @var string[]
     */
    protected $serviceProviders = [
        ServiceProvider::class,
    ];
}