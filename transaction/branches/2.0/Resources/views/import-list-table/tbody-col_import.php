<?php
/**
 * Colonne des informations d'import.
 * ---------------------------------------------------------------------------------------------------------------------
 * @var tiFy\Template\Templates\ListTable\Viewer $this
 * @var tiFy\Template\Templates\ListTable\Contracts\Column $column
 * @var tiFy\Plugins\Transaction\Template\ImportListTable\Contracts\Item $item
 * @var string $value
 */
?>
<?php if ($date = $item->importDate()) : ?>
    <?php echo $date->format('d/m/Y H:i:s'); ?>
<?php endif;