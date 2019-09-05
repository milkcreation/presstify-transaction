<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Template\Templates\ListTable\Contracts\RowAction;
use tiFy\Plugins\Parser\Template\FileListTable\FileListTableServiceProvider as BaseTemplateServiceProvider;

class ImportListTableServiceProvider extends BaseTemplateServiceProvider
{
    /**
     * Instance du gabarit d'affichage.
     * @var ImportListTable
     */
    protected $factory;

    /**
     * @inheritDoc
     */
    public function registerFactories(): void
    {
        parent::registerFactories();
        $this->registerFactoryRowActions();
    }

    /**
     * @inheritDoc
     */
    public function registerFactoryRowActions(): void
    {
        parent::registerFactoryRowActions();

        $this->getContainer()->share($this->getFactoryAlias('row-action.import'), function (): RowAction {
            $ctrl = $this->factory->get('providers.row-action.import');
            $ctrl = $ctrl instanceof RowAction
                ? $ctrl
                : new RowActionImport();

            return $ctrl->setTemplateFactory($this->factory);
        });
    }
}