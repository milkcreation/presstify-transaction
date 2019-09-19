<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWcProduct;

use tiFy\Plugins\Woocommerce\Query\QueryProduct;
use tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpPost\Item as BaseItem;
use tiFy\Template\Templates\ListTable\Contracts\Item as ItemContract;
use WP_Post;

/**
 * @mixin QueryProduct
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
    public function parse(): ItemContract
    {
        parent::parse();

        if ($this->exists() instanceof WP_Post) {
            $this->setDelegate(QueryProduct::createFromId($this->exists()->ID));
        }

        return $this;
    }
}