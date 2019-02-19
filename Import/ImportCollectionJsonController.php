<?php

namespace tiFy\Plugins\Transaction\Import;

use tiFy\Plugins\Transaction\TransactionResolverTrait;

class ImportCollectionJsonController extends ImportCollectionController
{
    use TransactionResolverTrait;

    /**
     * CONSTRUCTEUR.
     *
     * @param resource $filename Chemin absolu vers le fichier de données.
     * @param array $params Liste des paramètres d'import.
     *
     * @return void
     */
    public function __construct($filename = null, $params = [])
    {
        $filename = file_exists($filename) ? $filename : $this->resourcesDir('/example/email.json');
        $items = json_decode(file_get_contents($filename), true);

        parent::__construct($items, $params);
    }
}