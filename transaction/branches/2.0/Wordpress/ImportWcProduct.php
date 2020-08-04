<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress;

use tiFy\Plugins\Transaction\{
    Contracts\ImportRecord as BaseImportRecordContract,
    Wordpress\Contracts\ImportWpPost as ImportWpPostContract,
    Wordpress\Contracts\ImportWcProduct as ImportWcProductContract
};
use WP_Post, WC_Product, WC_Cache_Helper, WC_Product_Simple, WC_Product_Variable, WC_Product_Variation;

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
    public function clearCache(): ImportWpPostContract
    {
        parent::clearCache();

        if ($this->exists() instanceof WP_Post) {
            $id = $this->exists()->ID;

            wp_cache_delete("product-{$id}", 'products');

            $cache_key = WC_Cache_Helper::get_cache_prefix("product_{$id}") . "_type_{$id}";
            wp_cache_delete($cache_key, 'products');

            $cache_key = WC_Cache_Helper::get_cache_prefix('products') . WC_Cache_Helper::get_cache_prefix("object_{$id}") . "object_meta_{$id}";
            wp_cache_delete($cache_key, 'products');

            $this->product = wc_get_product($this->exists());
        }

        return $this;
    }

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

        if ($this->exists() instanceof WP_Post) {
            $this->product = wc_get_product($this->exists());
        }

        return $this;
    }
}