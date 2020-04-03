<?php
/**
 * Colonne des informations d'import.
 * ---------------------------------------------------------------------------------------------------------------------
 * @var tiFy\Contracts\Template\FactoryViewer $this
 * @var tiFy\Plugins\Transaction\Template\ImportListTable\Contracts\Item $item
 */
?>
<?php if ($date = $item->importDate()) : ?>
    <?php echo $date->format('d/m/Y H:i:s'); ?>
<?php endif;