<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpTerm;

use tiFy\Plugins\Transaction\Template\ImportListTable\ServiceProvider as BaseServiceProvider;
use tiFy\Template\Templates\ListTable\{
    Contracts\Item as BaseItemContract,
    Contracts\RowAction as BaseRowActionContract
};

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

    /**
     * @inheritDoc
     */
    public function registerFactoryRowActions(): void
    {
        parent::registerFactoryRowActions();

        $this->getContainer()->add($this->getFactoryAlias('row-action.edit'), function (): BaseRowActionContract {
            $ctrl = $this->factory->provider('row-action.edit');
            $ctrl = $ctrl instanceof BaseRowActionContract
                ? clone $ctrl
                : new RowActionEdit();

            return $ctrl->setTemplateFactory($this->factory);
        });
        
        $this->getContainer()->add($this->getFactoryAlias('row-action.show'), function (): BaseRowActionContract {
            $ctrl = $this->factory->provider('row-action.show');
            $ctrl = $ctrl instanceof BaseRowActionContract
                ? clone $ctrl
                : new RowActionShow();

            return $ctrl->setTemplateFactory($this->factory);
        });
    }
}