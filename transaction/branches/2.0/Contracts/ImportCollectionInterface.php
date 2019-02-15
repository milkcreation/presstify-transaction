<?php

namespace tiFy\Plugins\Transaction\Contracts;

use tiFy\Contracts\Kernel\Collection;
use tiFy\Contracts\Kernel\Logger;

interface ImportCollectionInterface extends Collection
{
    /**
     * {@inheritdoc}
     *
     * @return ImportItemInterface[]
     */
    public function all();

    /**
     * Action lancée après la tâche d'import.
     *
     * @return void
     */
    public function after();

    /**
     * Action lancée avant la tâche d'import.
     *
     * @return void
     */
    public function before();

    /**
     * {@inheritdoc}
     *
     * @return ImportItemInterface
     */
    public function get($key, $default = null);

    /**
     * Traitement de l'import de la liste des éléments.
     *
     * @return array Rapports des résultats de traitement des éléments.
     */
    public function import();

    /**
     * Traitement de l'import d'un élément.
     *
     * @param array $item Données de l'élément.
     *
     * @return array Rapport de traitement de l'élément.
     */
    public function importItem($item);

    /**
     * Récupération de l'instance du controleur de journalisation.
     *
     * @return Logger
     */
    public function log();
}