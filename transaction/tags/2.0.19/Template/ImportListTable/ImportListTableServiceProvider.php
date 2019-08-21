<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Plugins\Transaction\Template\ImportListTable\{
    Contracts\FileBuilder
};
use tiFy\Template\Templates\ListTable\Contracts\{Builder, DbBuilder};
use tiFy\Template\Templates\ListTable\ListTableServiceProvider as BaseListTableServiceProvider;

class ImportListTableServiceProvider extends BaseListTableServiceProvider
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
        $this->registerFactoryBuilder();
    }

    /**
     * Déclaration du controleur de gestion de traitement de fichier de données.
     *
     * @return void
     */
    public function registerFactoryBuilder(): void
    {
        $this->getContainer()->share($this->getFactoryAlias('builder'), function () {
            $ctrl = $this->factory->get('providers.builder');

            if ($source = $this->factory->param('source', [])) {
                $ctrl = $ctrl instanceof FileBuilder
                    ? clone $ctrl
                    : $this->getContainer()->get(FileBuilder::class);

                if (is_string($source)) {
                    $source = ['filename' => $source];
                }
                $ctrl->setSource($source);
            } elseif ($this->factory->db()) {
                $ctrl = $ctrl instanceof DbBuilder
                    ? clone $ctrl
                    : $this->getContainer()->get(DbBuilder::class);
            } else {
                $ctrl = $ctrl instanceof Builder
                    ? clone $ctrl
                    : $this->getContainer()->get(Builder::class);
            }

            $attrs = $this->factory->param('query_args', []);

            return $ctrl->setTemplateFactory($this->factory)->set(is_array($attrs) ? $attrs : []);
        });
    }
}