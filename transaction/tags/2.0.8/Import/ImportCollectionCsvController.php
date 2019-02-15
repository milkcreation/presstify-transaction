<?php

namespace tiFy\Plugins\Transaction\Import;

use tiFy\Plugins\Transaction\Stream\Csv;
use tiFy\Plugins\Transaction\TransactionResolverTrait;

class ImportCollectionCsvController extends ImportCollectionController
{
    use TransactionResolverTrait;

    /**
     * CONSTRUCTEUR.
     *
     * @param resource $filename Chemin absolu vers le fichier de données.
     * @param array $args Liste des arguments à passer au lecteur CSV.
     * @param array $params Liste des paramètres d'import.
     *
     * @return void
     */
    public function __construct($filename = null, $args = [], $params = [])
    {
        $defaults = [
            'filename' => file_exists($filename) ? $filename : $this->resourcesDir('/example/email.csv')
        ];
        $csv = Csv::getList(array_merge($defaults, $args));

        parent::__construct($csv->toArray(), $params);
    }
}