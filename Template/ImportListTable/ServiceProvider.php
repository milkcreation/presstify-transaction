<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Plugins\Parser\Template\FileListTable\{
    Contracts\Source as BaseSourceContract,
    ServiceProvider as BaseServiceProvider
};
use tiFy\Plugins\Transaction\{
    Contracts\ImportRecords as ImportRecordsContract,
    ImportRecords
};
use tiFy\Template\Templates\ListTable\{
    Contracts\Column as BaseColumnContract,
    Contracts\Item as BaseItemContract,
    Contracts\RowAction as BaseRowActionContract
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
        $this->registerFactoryRecords();
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
     * Déclaration des gestionnaires d'import des enregistrements.
     *
     * @return void
     */
    public function registerFactoryRecords(): void
    {
        $this->getContainer()->share($this->getFactoryAlias('records'), function (): ImportRecordsContract {
            $ctrl = $this->factory->provider('records');
            $ctrl = $ctrl instanceof ImportRecordsContract
                ? $ctrl
                : new ImportRecords();

            return $ctrl;
        });
    }

    /**
     * @inheritDoc
     */
    public function registerFactorySource(): void
    {
        $this->getContainer()->share($this->getFactoryAlias('source'), function (): BaseSourceContract {
            $ctrl = $this->factory->provider('source');

            if (!$attrs = $this->factory->param('source', [])) {
                return null;
            } else {
                $ctrl = $ctrl instanceof BaseSourceContract
                    ? clone $ctrl
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