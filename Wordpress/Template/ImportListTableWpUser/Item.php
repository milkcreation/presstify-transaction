<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpUser;

use tiFy\Wordpress\Query\QueryUser;
use tiFy\Plugins\Transaction\Template\ImportListTable\Item as BaseItem;
use tiFy\Template\Templates\ListTable\Contracts\Item as ItemContract;
use WP_User;

/**
 * @mixin QueryUser
 */
class Item extends BaseItem
{
    /**
     * Instance du gabarit associé.
     * @var Factory
     */
    protected $factory;

    /**
     * @inheritDoc
     */
    public function exists(): ?WP_User
    {
        return parent::exists();
    }

    /**
     * @inheritDoc
     */
    public function parse(): ItemContract
    {
        parent::parse();

        if ($this->exists() instanceof WP_User) {
            $this->setDelegate(QueryUser::createFromId($this->exists()->ID));
        }

        return $this;
    }
}