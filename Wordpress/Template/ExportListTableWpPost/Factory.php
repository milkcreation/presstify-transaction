<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ExportListTableWpPost;

use tiFy\Plugins\Transaction\Wordpress\Template\ExportListTableWpBase\Factory as BaseFactory;

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