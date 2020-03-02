<?php
/**
 * Interface de lancement de l'import.
 * ---------------------------------------------------------------------------------------------------------------------
 * @var tiFy\Contracts\Template\FactoryViewer $this
 */
echo partial('tag', $this->get('button', [])) . $this->get('handler', '');