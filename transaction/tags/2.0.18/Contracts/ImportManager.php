<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Contracts;

use tiFy\Contracts\Support\Collection;

interface ImportManager extends Collection
{
    /**
     * Action lancée à la fin du traitement de l'import.
     *
     * @return void
     */
    public function end(): void;

    /**
     * Traitement de l'import de la liste des éléments.
     *
     * @return array Rapports des résultats de traitement des éléments.
     */
    public function execute(): array;

    /**
     * Traitement de l'import d'un élément.
     *
     * @param ImportFactory $item Données de l'élément.
     *
     * @return array Rapport de traitement de l'élément.
     */
    public function executeItem(ImportFactory $item): array;

    /**
     * Action lancée au démarrage du traitement de l'import.
     *
     * @return void
     */
    public function start(): void;
}