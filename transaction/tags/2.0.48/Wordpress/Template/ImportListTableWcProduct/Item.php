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
        if (is_null($this->delegate) && $this->exists() instanceof WP_Post) {
            $this->setDelegate(QueryProduct::createFromId($this->exists()->ID));
        }

        parent::parse();

        return $this;
    }
}