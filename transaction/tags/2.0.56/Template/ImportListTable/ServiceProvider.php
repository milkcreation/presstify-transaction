<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Plugins\Parser\Template\FileListTable\{
    Contracts\Source as BaseSourceContract,
    ServiceProvider as BaseServiceProvider
};
use tiFy\Plugins\Transaction\{
    Contracts\ImportRecorder as ImportRecorderContract,
    ImportRecorder
};
use tiFy\Template\Templates\ListTable\{
    Contracts\Column as BaseColumnContract,
    Contracts\Extra as BaseExtraContract,
    Contracts\Params as BaseParamsContract,
    Contracts\RowAction as BaseRowActionContract
};
use tiFy\Plugins\Transaction\Template\ImportListTable\{
    Contracts\Actions as ActionsContract,
    Contracts\Item as ItemContract
};

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
        $this->registerFactoryRecorder();
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
    public function registerFactoryColumns(): void
    {
        parent::registerFactoryColumns();

        $this->getContainer()->add(
            $this->getFactoryAlias('column.import'),
            function (string $name, array $attrs = []): BaseColumnContract {
                return (new ColumnImport())
                    ->setTemplateFactory($this->factory)
                    ->setName($name)->set($attrs)->parse();
            });
    }

    /**
     * @inheritDoc
     */
    public function registerFactoryExtras(): void
    {
        parent::registerFactoryExtras();

        $this->getContainer()->add($this->getFactoryAlias('extra.import'), function (): BaseExtraContract {
            return (new ExtraImport())->setTemplateFactory($this->factory);
        });
    }

    /**
     * @inheritDoc
     */
    public function registerFactoryItem(): void
    {
        $this->getContainer()->add($this->getFactoryAlias('item'), function (): ItemContract {
            $ctrl = $this->factory->provider('item');
            $ctrl = $ctrl instanceof ItemContract
                ? clone $ctrl
                : new Item();

            return $ctrl->setTemplateFactory($this->factory);
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

        $this->getContainer()->add($this->getFactoryAlias('row-action.import'), function (): BaseRowActionContract {
            $ctrl = $this->factory->provider('row-action.import');
            $ctrl = $ctrl instanceof BaseRowActionContract
                ? clone $ctrl
                : new RowActionImport();

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

    /**
     * Déclaration des gestionnaires d'enregistrements.
     *
     * @return void
     */
    public function registerFactoryRecorder(): void
    {
        $this->getContainer()->share($this->getFactoryAlias('recorder'), function (): ImportRecorderContract {
            $ctrl = $this->factory->provider('recorder');
            $ctrl = $ctrl instanceof ImportRecorderContract
                ? $ctrl
                : new ImportRecorder();

            return $ctrl;
        });
    }

    /**
     * @inheritDoc
     */
    public function registerFactorySource(): void
    {
        $this->getContainer()->share($this->getFactoryAlias('source'), function (): ?BaseSourceContract {
            $ctrl = $this->factory->provider('source');

            if (!$attrs = $this->factory->param('source', [])) {
                return null;
            } else {
                $ctrl = $ctrl instanceof BaseSourceContract
                    ? $ctrl
                    : new Source();
            }

            if(is_string($attrs)) {
                if (is_file($attrs)) {
                    $attrs = ['filename' => $attrs];
                } else {
                    $attrs = ['dir' => $attrs];
                }
            }

            return $ctrl->setTemplateFactory($this->factory)->set(is_array($attrs) ? $attrs : []);
        });
    }
}