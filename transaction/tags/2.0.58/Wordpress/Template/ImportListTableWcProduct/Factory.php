<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWcProduct;

use tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpBase\Factory as BaseFactory;

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