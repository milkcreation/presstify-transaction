<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ExportListTable;

use tiFy\Plugins\Transaction\Proxy\Transaction;
use tiFy\Template\Templates\ListTable\{Contracts\Extra as BaseExtraContract, Extra};
use tiFy\Support\Proxy\View as ProxyView;
use tiFy\Template\Factory\View;

class ExtraExport extends Extra
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
                'attrs'   => [
                    'type' => 'submit',
                    'name' => $this->factory->actions()->getIndex(),
                    'value' => 'export'
                ],
                'tag'     => 'button',
                'content' => __('Lancer l\'export', 'tify'),
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
                'button.attrs.data-control'   => 'list-table.export-rows',
                'cancel.attrs.data-control'   => 'list-table.export-rows.cancel',
                'progress.attrs.data-control' => 'list-table.export-rows.progress',
            ]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        $view = ProxyView::getPlatesEngine([
            'directory' => Transaction::resourcesDir('/views/export-list-table'),
            'factory'   => View::class,
        ]);

        if ($this->factory->ajax() && !static::$progress++) {
            $this->set('handler', $view->render('export-handler', $this->all()));
        } else {
            $this->forget('handler');
        }

        return $view->render('extra-export', $this->all());
    }
}