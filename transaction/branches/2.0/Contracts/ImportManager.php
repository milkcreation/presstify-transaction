<?php

namespace tiFy\Plugins\Transaction\Contracts;

use tiFy\Contracts\Support\Collection;
use tiFy\Contracts\Kernel\Logger;

interface ImportManager extends Collection
{
    /**
     * {@inheritdoc}
     *
     * @return ImportFactory[]
     */
    public function all();

    /**
     * Action lancée à la fin du traitement de l'import.
     *
     * @return void
     */
    public function end();

    /**
     * {@inheritdoc}
     *
     * @return ImportFactory
     */
    public function get($key);

    /**
     * Traitement de l'import de la liste des éléments.
     *
     * @return array Rapports des résultats de traitement des éléments.
     */
    public function handle();

    /**
     * Traitement de l'import d'un élément.
     *
     * @param ImportFactory $item Données de l'élément.
     *
     * @return array Rapport de traitement de l'élément.
     */
    public function handleItem(ImportFactory $item);

    /**
     * Récupération de l'instance du controleur de journalisation ou ajout d'un message.
     *
     * @param null|string $type de message success|info|warning|error
     * @param string $message Contenu du message de notification.
     * @param array $context Données de contexte.
     *
     * @return boolean|Logger
     */
    public function log($type = null, $message = '', $context = []);

    /**
     * Action lancée au démarrage du traitement de l'import.
     *
     * @return void
     */
    public function start();
}