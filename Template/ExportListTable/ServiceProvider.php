<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ExportListTable;

use tiFy\Template\Templates\ListTable\Contracts\{
    Extra as BaseExtraContract,
    Params as BaseParamsContract
};
use tiFy\Plugins\Transaction\Template\ExportListTable\Contracts\Actions as ActionsContract;
use tiFy\Template\Templates\ListTable\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Instance du gabarit associé.
     * @var Factory
     */
    protected $factory;

    /**
     * @inheritDoc
     */
    public function registerFactories(): void
    {
        parent::registerFactories();
    }

    /**
     * @inheritDoc
     */
    public function registerFactoryActions(): void
    {
        $this->getContainer()->share($this->getFactoryAlias('actions'), function (): ActionsContract {
            $ctrl = $this->factory->provider('actions');
            $ctrl = $ctrl instanceof ActionsContract
                ? $ctrl
                : new Actions();

            return $ctrl->setTemplateFactory($this->factory);
        });
    }

    /**
     * @inheritDoc
     */
    public function registerFactoryExtras(): void
    {
        parent::registerFactoryExtras();

        $this->getContainer()->add($this->getFactoryAlias('extra.export'), function (): BaseExtraContract {
            return (new ExtraExport())->setTemplateFactory($this->factory);
        });
    }

    /**
     * @inheritDoc
     */
    public function registerFactoryParams(): void
    {
        $this->getContainer()->share($this->getFactoryAlias('params'), function (): BaseParamsContract {
            $ctrl = $this->factory->provider('params');
            $ctrl = $ctrl instanceof BaseParamsContract
                ? $ctrl
                : new Params();

            $attrs = $this->factory->get('params', []);

            return $ctrl->setTemplateFactory($this->factory)->set(is_array($attrs) ? $attrs : [])->parse();
        });
    }
}