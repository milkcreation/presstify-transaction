<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress;

use tiFy\Plugins\Transaction\{
    Contracts\ImportFactory as BaseImportFactoryContract,
    Wordpress\Contracts\ImportFactoryWcProduct as ImportFactoryWcProductContract
};
use WC_Product;
use WC_Product_Simple;
use WC_Product_Variable;
use WC_Product_Variation;

class ImportFactoryWcProduct extends ImportFactoryWpPost implements ImportFactoryWcProductContract
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
     * @return ImportFactoryWcProductContract
     */
    public function setPrimary($primary): BaseImportFactoryContract
    {
        parent::setPrimary($primary);

        $this->product = wc_get_product($primary);

        return $this;
    }
}