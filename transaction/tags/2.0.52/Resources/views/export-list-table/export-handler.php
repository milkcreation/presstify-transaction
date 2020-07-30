<?php
/**
 * Interface de progression de l'export.
 * ---------------------------------------------------------------------------------------------------------------------
 * @var tiFy\Contracts\Template\FactoryViewer $this
 */
?>
<div class="ListTable-ExportHandler" data-control="list-table.export-rows.handler">
    <?php echo partial('progress', $this->get('progress', [])); ?>

    <?php echo partial('tag', $this->get('cancel', [])); ?>
</div>