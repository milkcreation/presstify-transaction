<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ExportListTableWpBase;

use tiFy\Plugins\Transaction\Template\ExportListTable\Factory as BaseFactory;

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