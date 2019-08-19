<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Import;

use tiFy\Plugins\Transaction\{
    Contracts\ImportFactory as ImportFactoryContract,
    Contracts\ImportManager as ImportManagerContract
};
use tiFy\Support\{
    Collection,
    ParamsBag
};

class Manager extends Collection implements ImportManagerContract
{
    /**
     * Classe de traitement d'un élément (requis).
     * @var string
     */
    protected $factoryClass = Factory::class;

    /**
     * Liste des éléments du fichier.
     * @var ImportFactoryContract[]
     */
    protected $items = [];

    /**
     * Instance du controleur de gestion des paramètres d'import.
     * @var ParamsBag
     */
    protected $params;

    /**
     * CONSTRUCTEUR.
     *
     * @param array $items Liste des éléments à traiter.
     *
     * @return void
     */
    public function __construct($items = [], $params = [])
    {
        $this->params = params($params);

        $this->set($items);
    }

    /**
     * @inheritDoc
     */
    public function end(): void {}

    /**
     * @inheritDoc
     */
    public function execute(): array
    {
        $this->start();

        $results = [];
        foreach($this->all() as $i => $item) {
            $results[] = $this->executeItem($item);
        }

        $this->end();

        return $results;
    }

    /**
     * @inheritDoc
     */
    public function executeItem(ImportFactoryContract $item): array
    {
        $result = $item->execute();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function start(): void {}

    /**
     * @inheritDoc
     */
    public function walk($item, $key = null)
    {
        $this->items[$key] = new $this->factoryClass($item, $this);
    }
}