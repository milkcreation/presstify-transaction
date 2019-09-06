<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Plugins\Parser\Template\FileListTable\FileListTableServiceProvider as BaseTemplateServiceProvider;
use tiFy\Plugins\Transaction\{
    Contracts\ImportRecords as ImportRecordsContract,
    ImportRecords
};
use tiFy\Template\Templates\ListTable\Contracts\RowAction;

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
        $this->registerFactoryRecords();
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

    /**
     * Déclaration des gestionnaires d'import des enregistrements.
     *
     * @return void
     */
    public function registerFactoryRecords(): void
    {
        $this->getContainer()->share($this->getFactoryAlias('records'), function (): ImportRecordsContract {
            $ctrl = $this->factory->get('providers.records');
            $ctrl = $ctrl instanceof ImportRecordsContract
                ? $ctrl
                : new ImportRecords();

            if ($source = $this->factory->source()) {
                $ctrl->setReader($source->reader())->fetch();
            }

            return $ctrl;
        });
    }
}