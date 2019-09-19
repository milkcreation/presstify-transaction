<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpBase;

use tiFy\Plugins\Transaction\Template\ImportListTable\ServiceProvider as BaseServiceProvider;
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

        $this->getContainer()->add($this->getFactoryAlias('extra.full-import'), function (): BaseExtraContract {
            return (new ExtraImport())->setTemplateFactory($this->factory);
        });
    }
}