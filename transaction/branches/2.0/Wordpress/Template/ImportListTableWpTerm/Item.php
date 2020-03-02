<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpTerm;

use tiFy\Wordpress\Query\QueryTerm;
use tiFy\Plugins\Transaction\Template\ImportListTable\Item as BaseItem;
use tiFy\Template\Templates\ListTable\Contracts\Item as ItemContract;
use WP_Term;

/**
 * @mixin QueryTerm
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
    public function exists(): ?WP_Term
    {
        return parent::exists();
    }

    /**
     * @inheritDoc
     */
    public function parse(): ItemContract
    {
        if (is_null($this->delegate) && $this->exists() instanceof WP_Term) {
            $this->setDelegate(QueryTerm::createFromId($this->exists()->term_id));
        }

        parent::parse();

        return $this;
    }
}