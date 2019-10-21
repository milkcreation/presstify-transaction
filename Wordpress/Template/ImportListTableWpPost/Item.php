<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpPost;

use tiFy\Wordpress\Query\QueryPost;
use tiFy\Plugins\Transaction\Template\ImportListTable\Item as BaseItem;
use tiFy\Template\Templates\ListTable\Contracts\Item as ItemContract;
use WP_Post;

/**
 * @mixin QueryPost
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
    public function exists(): ?WP_Post
    {
        return parent::exists();
    }

    /**
     * @inheritDoc
     */
    public function parse(): ItemContract
    {
        parent::parse();

        if ($this->exists() instanceof WP_Post) {
            $this->setDelegate(QueryPost::createFromId($this->exists()->ID));
        }

        return $this;
    }
}