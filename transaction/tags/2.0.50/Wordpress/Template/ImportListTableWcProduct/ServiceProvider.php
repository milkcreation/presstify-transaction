<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWcProduct;

use tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpPost\ServiceProvider as BaseServiceProvider;
use tiFy\Template\Templates\ListTable\Contracts\Item as BaseItemContract;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Instance du gabarit d'affichage.
     * @var Factory
     */
    protected $factory;

    /**
     * @inheritDoc
     */
    public function registerFactoryItem(): void
    {
        $this->getContainer()->add($this->getFactoryAlias('item'), function (): BaseItemContract {
            $ctrl = $this->factory->provider('item');
            $ctrl = $ctrl instanceof BaseItemContract
                ? clone $ctrl
                : new Item();

            return $ctrl->setTemplateFactory($this->factory);
        });
    }
}