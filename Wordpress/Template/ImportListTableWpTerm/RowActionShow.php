<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpTerm;

use tiFy\Plugins\Transaction\Template\ImportListTable\RowActionShow as BaseRowActionShow;

class RowActionShow extends BaseRowActionShow
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
            'url'     => function (Item $item) {
                return $item->exists() ? url_factory($item->getPermalink()): null;
            },
        ]);
    }
}