<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpPost;

use tiFy\Plugins\Transaction\Template\ImportListTable\RowActionShow as BaseRowActionShow;
use tiFy\Support\Proxy\Url;

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
                return $item->exists() ? Url::set($item->getPermalink()): null;
            },
        ]);
    }
}