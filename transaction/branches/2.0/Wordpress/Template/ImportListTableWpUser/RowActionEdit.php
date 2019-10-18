<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpUser;

use tiFy\Plugins\Transaction\Template\ImportListTable\RowActionEdit as BaseRowActionEdit;
use tiFy\Support\Proxy\Url;

class RowActionEdit extends BaseRowActionEdit
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
            'url' => function (Item $item) {
                return $item->exists() ? Url::set(get_edit_user_link($item->getId())) : null;
            },
        ]);
    }
}