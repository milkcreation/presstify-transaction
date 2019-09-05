<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Contracts;

use tiFy\Plugins\Transaction\Contracts\ImportFactory;
use WC_Product;
use WC_Product_Simple;
use WC_Product_Variable;
use WC_Product_Variation;

interface ImportFactoryWcProduct extends ImportFactory
{
    /**
     * Récupération de l'instance du produit Woocommerce associé.
     *
     * @return WC_Product|WC_Product_Simple|WC_Product_Variable|WC_Product_Variation|null
     */
    public function getProduct(): ?WC_Product;
}