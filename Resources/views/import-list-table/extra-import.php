<?php
/**
 * Interface de lancement de l'import.
 * ---------------------------------------------------------------------------------------------------------------------
 * @var tiFy\Contracts\View\ViewController $this
 */
echo partial('tag', $this->get('button', [])) . $this->get('handler', '');