<?php

namespace tiFy\Plugins\Transaction\Import;

use tiFy\Contracts\Kernel\Logger;
use tiFy\Contracts\Kernel\ParamsBag;
use tiFy\Kernel\Collection\Collection;
use tiFy\Plugins\Transaction\Contracts\ImportManager as ImportManagerContract;
use tiFy\Plugins\Transaction\Contracts\ImportFactory as ImportFactoryContract;

class ImportManager extends Collection implements ImportManagerContract
{
    /**
     * Classe de traitement d'un élément (requis).
     * @var string
     */
    protected $factoryClass = ImportFactory::class;

    /**
     * Liste des éléments du fichier.
     * @var ImportFactoryContract[]
     */
    protected $items = [];

    /**
     * Instance du controleur de journalisation.
     * @var null|Logger
     */
    protected $logger;

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

        array_walk($items, [$this, 'wrap']);
    }

    /**
     * @inheritdoc
     */
    public function end()
    {

    }

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $this->start();

        $results = [];
        foreach($this->all() as $i => $item) :
            $results[] = $this->handleItem($item);
        endforeach;

        $this->end();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function handleItem(ImportFactoryContract $item)
    {
        $result = $item->proceed();

        if ($this->log()) :
            foreach($result['notices'] as $type => $notices) :
                foreach($notices as $id => $notice) :
                    call_user_func([$this->log(), $type], $notice['message'], $notice['datas']);
                endforeach;
            endforeach;
        endif;

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function log($type = null, $message = '', $context = [])
    {
        if (is_null($this->logger)) :
            if ($this->logger = $this->params->get('logger', true)) :
                if (!$this->logger instanceof Logger) :
                    $defaults = ['name' => 'import'];

                    $this->logger = is_array($this->logger)
                        ? array_merge($defaults, $this->logger)
                        : $defaults;

                    $this->logger = app('logger', [$this->logger['name'], $this->logger]);
                endif;
            endif;
        endif;

        if(is_null($type)) :
            return $this->logger;
        elseif ($this->logger instanceof Logger) :
            return call_user_func([$this->logger, $type], $message, $context);
        endif;

        return false;
    }

    /**
     * @inheritdoc
     */
    public function start()
    {

    }

    /**
     * @inheritdoc
     */
    public function wrap($item, $key = null)
    {
        $this->items[$key] = new $this->factoryClass($item, $this);
    }
}