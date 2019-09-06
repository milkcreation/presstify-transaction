<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress;

use tiFy\Plugins\Transaction\{
    Contracts\ImportRecord as BaseImportRecordContract,
    Wordpress\Contracts\ImportRecordWcProduct as ImportRecordWcProductContract
};
use WC_Product;
use WC_Product_Simple;
use WC_Product_Variable;
use WC_Product_Variation;

class ImportRecordWcProduct extends ImportRecordWpPost implements ImportRecordWcProductContract
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
     * @return ImportRecordWcProductContract
     */
    public function setPrimary($primary): BaseImportRecordContract
    {
        parent::setPrimary($primary);

        $this->product = wc_get_product($primary);

        return $this;
    }
}