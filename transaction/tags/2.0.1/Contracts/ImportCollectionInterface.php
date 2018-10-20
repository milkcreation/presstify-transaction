<?php

namespace tiFy\Plugins\Transaction\Contracts;

use Psr\Log\LoggerInterface;

interface ImportCollectionInterface
{
    /**
     * Récupération de la liste des éléments
     *
     * @return ImportItemInterface[]
     */
    public function all();

    /**
     * Récupération des données d'un élément.
     *
     * @param string $key Identifiant de qualification de l'élément.
     *
     * @return ImportItemInterface
     */
    public function get($key);

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
     * @return LoggerInterface
     */
    public function log();

    /**
     * Traitement de la liste des éléments.
     *
     * @param array $items Liste des éléments.
     *
     * @return ImportItemInterface[]
     */
    public function parse($items = []);
}