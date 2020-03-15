<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Plugins\Transaction\Proxy\Transaction;
use tiFy\Support\Proxy\Url;
use tiFy\Template\Templates\ListTable\{Contracts\Extra as BaseExtraContract, Extra};
use tiFy\Support\Proxy\View as ProxyView;
use tiFy\Template\Factory\View;

class ExtraImport extends Extra
{
    /**
     * Indicateur d'instanciation de la barre de progression.
     * @var int
     */
    protected static $progress = 0;

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
            'button'   => [
                'tag'     => 'a',
                'content' => __('Lancer l\'import', 'tify'),
            ],
            'progress' => [],
            'cancel'   => [
                'attrs'   => ['type' => 'button'],
                'content' => __('Annuler', 'tify'),
                'tag'     => 'button',
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
                'button.attrs.data-control' => 'list-table.import-rows',
                'button.attrs.href'         => Url::set($this->factory->baseUrl() . '/xhr')->with([
                    'action' => 'import',
                ]),
            ]);
            $this->set('progress.attrs.data-control', 'list-table.import-rows.progress');
            $this->set('cancel.attrs.data-control', 'list-table.import-rows.cancel');
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $view = ProxyView::getPlatesEngine([
            'directory' => Transaction::resourcesDir('/views/import-list-table'),
            'factory'   => View::class
        ]);

        if (!static::$progress++) {
            $this->set('handler', $view->render('import-handler', $this->all()));
        } else {
            $this->forget('handler');
        }

        return $view->render('extra-import', $this->all());
    }
}