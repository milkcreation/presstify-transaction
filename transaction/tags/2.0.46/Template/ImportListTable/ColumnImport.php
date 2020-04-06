<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use Closure;
use tiFy\Support\Proxy\View as ProxyView;
use tiFy\Template\Factory\View;
use tiFy\Template\Templates\ListTable\Column as BaseColumn;
use tiFy\Plugins\Transaction\Proxy\Transaction;

class ColumnImport extends BaseColumn
{
    /**
     * Instance du gabarit associé.
     * @var Factory
     */
    protected $factory;

    /**
     * @inheritDoc
     */
    public function canUseForPrimary(): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return [
            'title' => __('Import', 'tify')
        ];
    }

    /**
     * @inheritDoc
     */
    public function render(): string
    {
        if ($item = $this->factory->item()) {
            $classes = '';
            if ($this->isPrimary()) {
                $classes .= 'has-row-actions column-primary';
            }

            if ($this->isHidden()) {
                $classes .= 'hidden';
            }

            if ($classes) {
                $this->set('attrs.class', trim($this->get('attrs.class', '') . " {$classes}"));
            }

            $row_actions = (string)($this->isPrimary() ? $this->factory->rowActions() : '');

            $args = [
                'item'        => $item,
                'value'       => $this->value() . $row_actions,
                'column'      => $this,
                'row_actions' => $row_actions,
            ];

            if (($content = $this->get('content')) instanceof Closure) {
                return call_user_func_array($content, $args);
            } else {
                $view = ProxyView::getPlatesEngine([
                    'directory' => Transaction::dir('/views/import-list-table'),
                    'factory'   => View::class
                ]);

                return $view->render('col-import', $args);
            }
        } else {
            return '';
        }
    }
}