<?php

namespace tiFy\Plugins\Transaction\Import;

use Illuminate\Support\Collection;
use tiFy\Contracts\Kernel\ParamsBag;
use tiFy\Plugins\Transaction\Contracts\ImportCollectionInterface;
use tiFy\Plugins\Transaction\Contracts\ImportItemInterface;

class ImportCollectionController implements ImportCollectionInterface
{
    /**
     * Liste des éléments du fichier.
     * @var Collection|ImportItemInterface[]
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
     * @param array $params Liste des paramètres d'import.
     *
     * @return void
     */
    public function __construct($items = [], $params = [])
    {
        $this->params = app('params.bag', [$params]);
        $logger = $this->params->get('logger');
        if (!$logger instanceof LoggerInterface) :
            $defaults = ['name' => 'import'];

            $logger = is_array($logger)
                ? array_merge($defaults, $logger)
                : $defaults;

            $this->params->set(
                'logger',
                app('logger', [$logger['name'], $logger])
            );
        endif;

        $this->items = $this->parse($items);
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->items->all();
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return $this->items->get($key, []);
    }

    /**
     * {@inheritdoc}
     */
    public function import()
    {
        $results = [];
        foreach($this->all() as $item) :
            $res = $this->importItem($item);

            foreach($res['notices'] as $type => $notices) :
                foreach($notices as $id => $notice) :
                    call_user_func([$this->log(), $type], $notice['message'], $notice['datas']);
                endforeach;
            endforeach;

            $results[] = $res;
        endforeach;

        return $results;
    }

    /**
     * {@inheritdoc}
     */
    public function importItem($item)
    {
        return (new ImportItemController($item))->proceed();
    }

    /**
     * {@inheritdoc}
     */
    public function log()
    {
        return $this->params->get('logger');
    }

    /**
     * {@inheritdoc}
     */
    public function parse($items = [])
    {
        return new Collection($items);
    }
}