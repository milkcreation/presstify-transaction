<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress;

use tiFy\Plugins\Transaction\{
    Contracts\ImportRecord as BaseImportRecordContract,
    Wordpress\Contracts\ImportWcProduct as ImportWcProductContract
};
use WP_Post, WC_Product, WC_Product_Simple, WC_Product_Variable, WC_Product_Variation;

class ImportWcProduct extends ImportWpPost implements ImportWcProductContract
{
    /**
     * Instance du produit woocommerce associé.
     * @var WC_Product|WC_Product_Simple|WC_Product_Variable|WC_Product_Variation|null
     */
    protected $product;

    /**
     * @inheritDoc
     */
    public function getProduct(): ?WC_Product
    {
        return $this->product;
    }

    /**
     * {@inheritDoc}
     *
     * @return ImportWcProductContract
     */
    public function setExists($exists = null): BaseImportRecordContract
    {
        parent::setExists($exists);

        if ($this->exists instanceof WP_Post) {
            $this->product = wc_get_product($this->exists);
        }

        return $this;
    }
}