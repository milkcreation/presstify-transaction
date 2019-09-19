<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Template\Templates\ListTable\{
    Contracts\Extra as BaseExtraContract,
    Extra
};
use tiFy\Plugins\Transaction\Proxy\Transaction;

class ExtraImport extends Extra
{
    /**
     * Instance du gabarit associé.
     * @var Factory
     */
    protected $factory;

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return array_merge(parent::defaults(), [
            'button' => [
                'tag'     => 'a',
                'content' => __('Lancer l\'import', 'theme'),
            ],
        ]);
    }

    /**
     * @inheritDoc
     */
    public function parse(): BaseExtraContract
    {
        parent::parse();

        if ($this->factory->ajax()) {
            $this->set([
                'button.attrs.data-control' => 'list-table.full-import',
                'button.attrs.href' => url_factory($this->factory->baseUrl() . '/xhr')->with([
                    'action' => 'import',
                    'full'   => '1'
                ])
            ]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        return (string)view()
            ->setDirectory(Transaction::resourcesDir('/views/import-list-table'))
            ->make('extra-import', $this->all());
    }
}