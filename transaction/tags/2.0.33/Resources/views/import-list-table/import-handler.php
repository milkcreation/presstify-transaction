<?php
/**
 * Interface de progression de l'import.
 * ---------------------------------------------------------------------------------------------------------------------
 * @var tiFy\Contracts\View\ViewController $this
 */
?>
<div class="ListTable-ImportHandler" data-control="list-table.import-rows.handler">
    <?php echo partial('progress', $this->get('progress', [])); ?>

    <?php echo partial('tag', $this->get('cancel', [])); ?>
</div>