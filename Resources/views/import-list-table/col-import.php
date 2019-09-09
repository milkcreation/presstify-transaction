<?php
/**
 * Colonne des informations d'import.
 * ---------------------------------------------------------------------------------------------------------------------
 * @var tiFy\Plugins\Transaction\Template\ImportListTable\Contracts\Item $item
 */
?>
<?php echo $item->getId(); ?><br>
<?php if ($date = $item->importDate()) : ?>
    <?php echo $date->format('d/m/Y H:i:s'); ?>
<?php endif;