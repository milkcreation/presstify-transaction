<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ExportListTableWpBase;

use tiFy\Plugins\Transaction\Template\ExportListTable\ServiceProvider as BaseServiceProvider;
use tiFy\Template\Templates\ListTable\Contracts\Extra as BaseExtraContract;

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
    public function registerFactoryExtras(): void
    {
        parent::registerFactoryExtras();

        $this->getContainer()->add($this->getFactoryAlias('extra.export'), function (): BaseExtraContract {
            return (new ExtraExport())->setTemplateFactory($this->factory);
        });
    }
}