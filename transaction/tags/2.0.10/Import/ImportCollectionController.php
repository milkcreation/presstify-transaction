<?php

namespace tiFy\Plugins\Transaction\Import;

use tiFy\Contracts\Kernel\Logger;
use tiFy\Contracts\Kernel\ParamsBag;
use tiFy\Kernel\Collection\Collection;
use tiFy\Plugins\Transaction\Contracts\ImportCollectionInterface;
use tiFy\Plugins\Transaction\Contracts\ImportItemInterface;

class ImportCollectionController extends Collection implements ImportCollectionInterface
{
    /**
     * Liste des éléments du fichier.
     * @var ImportItemInterface[]
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
        $this->params = params($params);

        $logger = $this->params->get('logger');
        if (!$logger instanceof Logger) :
            $defaults = ['name' => 'import'];

            $logger = is_array($logger)
                ? array_merge($defaults, $logger)
                : $defaults;

            $this->params->set(
                'logger',
                app('logger', [$logger['name'], $logger])
            );
        endif;

        array_walk($items, [$this, 'wrap']);
    }

    /**
     * @inheritdoc
     */
    public function after()
    {

    }

    /**
     * @inheritdoc
     */
    public function before()
    {

    }

    /**
     * @inheritdoc
     */
    public function import()
    {
        $this->before();

        $results = [];
        foreach($this->all() as $i => $item) :
            $res = $this->importItem($item);

            foreach($res['notices'] as $type => $notices) :
                foreach($notices as $id => $notice) :
                    call_user_func([$this->log(), $type], $notice['message'], $notice['datas']);
                endforeach;
            endforeach;

            $results[] = $res;
        endforeach;

        $this->after();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function importItem($item)
    {
        return (new ImportItemController($item))->proceed();
    }

    /**
     * @inheritdoc
     */
    public function log()
    {
        return $this->params->get('logger');
    }
}