<?php

namespace tiFy\Plugins\Transaction\ImportItem;

use Illuminate\Support\Arr;
use tiFy\Contracts\Kernel\NoticesInterface;
use tiFy\Kernel\Parameters\AbstractParametersBag;
use tiFy\Plugins\Transaction\Contracts\ImportItemInterface;

abstract class AbstractImportItemController extends AbstractParametersBag implements ImportItemInterface
{
    /**
     * Indicateur d'interruption de l'exécution.
     * @var boolean
     */
    protected $break = false;

    /**
     * Cartographie des clés de données de sortie autorisées à être traitée.
     * @var array
     */
    protected $constraint = [
        'data' => [],
        'meta' => []
    ];

    /**
     * Liste des données d'entrées brutes.
     * @var array
     */
    protected $input = [];

    /**
     * Cartographie des données à traiter.
     * {@internal Tableau indexé|Tableau dimensionné :
     *  - Tableau indexé : ['key1', 'key2', ...]. La clé de données de sortie et la clé de donnée d'entrée sont identiques.
     *  - Tableau dimensionné : ['outputkey1' => 'inputkey1', 'outputkey2' => 'inputkey2', ...]. La clé de données de sortie et la clé de donnée d'entrée sont différentes.
     * }
     * @var array
     */
    protected $map = [
        'data' => [],
        'meta' => []
    ];

    /**
     * Instance de la classe de traitement des messages de notification.
     * @var NoticesInterface
     */
    protected $notices;

    /**
     * Liste des données de sortie à traiter.
     * @var array
     */
    protected $output = [];

    /**
     * Valeur de clé primaire de l'élément.
     * {@internal Déclenche la mise à jour si définie, sinon crée un nouvel élément.}
     * @var mixed
     */
    protected $primaryId = null;

    /**
     * Indicateur de succés de la tâche.
     * @var boolean
     */
    protected $success = false;

    /**
     * Types de données pris en charge.
     * @var array {
     *      @var string $data Données principales
     * }
     */
    protected $types = ['data'];

    /**
     * CONSTRUCTEUR.
     *
     * @param array $input Liste des données d'entrées brutes.
     * @param array $attrs Liste des attributs de configuration.
     *
     * @return void
     */
    public function __construct($input = [], $attrs = [])
    {
        $this->input = $this->parseInput($input);

        parent::__construct($attrs);

        $this->boot();
    }

    /**
     * Définition des valeurs des données de sortie à traiter.
     *
     * @return void
     */
    private function _outputSet()
    {
        foreach ($this->getTypes() as $type) :
            $Type = ucfirst($type);

            if ($customMap = call_user_func([$this, "map{$Type}"])) :
                Arr::set($this->map, $type, $customMap);
            endif;

            if ($map = $this->getMap($type)) :
                foreach ($map as $output_key => $input_key) :
                    $this->_outputSetValue($type, $output_key, $input_key);
                endforeach;
            elseif ($type === 'data') :
                foreach (array_keys($this->input) as $key) :
                    $this->_outputSetValue($type, $key, $key);
                endforeach;
            endif;
        endforeach;
    }

    /**
     * Définition d'un valeur de donnée de sortie à traiter.
     *
     * @param string $type Nom de qualification du type de données.
     * @param string $output_key Clé d'indexe de la donnée de sortie.
     * @param string $input_key Clé d'indexe de la donnée d'entrée.
     *
     * @return void
     */
    private function _outputSetValue($type, $output_key, $input_key)
    {
        $Type = ucfirst($type);

        if (is_numeric($output_key)) :
            $output_key = $input_key;
        endif;

        $constraint = Arr::get($this->constraint, $type, []);
        if ($constraint && !in_array($output_key, $constraint)) :
            return;
        endif;

        if (is_array($input_key)) :
            $raw_value = [];
            foreach($input_key as $key) :
                $raw_value[] = Arr::get($this->input, $key);
            endforeach;
        else :
            $raw_value = Arr::get($this->input, $input_key);
        endif;

        $value = call_user_func_array(
            [$this, "outputSet{$Type}"],
            [$output_key, $raw_value]
        );

        Arr::set($this->output, "{$type}.{$output_key}", $value);
    }

    /**
     * Contrôle des valeurs des données de sortie selon le type.
     *
     * @param string $type Nom de qualification du type de données.
     *
     * @return void
     */
    private function _outputTypeCheck($type, $primary_id = null)
    {
        $Type = ucfirst($type);

        foreach(Arr::get($this->output, $type, []) as $key => &$value) :
            $res = call_user_func_array(
                [$this, "outputCheck{$Type}"],
                [$key, $value, $this->primaryId]
            );
        endforeach;
    }

    /**
     * Filtrage des valeurs des données de sortie selon le type.
     *
     * @param string $type Nom de qualification du type de données.
     *
     * @return void
     */
    private function _outputTypeFilter($type, $primary_id = null)
    {
        $Type = ucfirst($type);

        $values = [];
        foreach(Arr::get($this->output, $type, []) as $key => $value) :
            $values[$key] = call_user_func_array(
                [$this, "outputFilter{$Type}"],
                [$key, $value, $this->primaryId]
            );
        endforeach;

        Arr::set($this->output, $type, $values);
    }

    /**
     * Lancement du traitement.
     *
     * @param array $input Liste des attributs de données d'entrée.
     * @param array $attrs Attributs de configuration de traitement.
     *
     * @return void
     */
    public static function make($input = [], $attrs = [])
    {
        $i = new static($input, $attrs);

        while(!$i->break) :
            // Définition des valeurs des données de sortie à traiter.
            $i->_outputSet();
            if ($i->onBreak()) break;

            // Evénement pré-insertion.
            $i->before($i->getPrimaryId());
            if ($i->onBreak()) break;

            // Filtrage des données principales de sorties.
            $i->_outputTypeFilter('data');
            if ($i->onBreak()) break;

            // Vérification des données principales de sorties.
            $i->_outputTypeCheck('data');
            if ($i->onBreak()) break;

            // Evénement pré-insertion des données principales de sorties.
            $i->insertDataBefore($i->getOutputData(), $i->getPrimaryId());
            if ($i->onBreak()) break;

            // Insertion des données principales de sorties.
            $i->insertData($i->getOutputData(), $i->getPrimaryId());
            if ($i->onBreak()) break;

            // Evénement post-insertion des données principales de sorties
            $i->insertDataAfter($i->getOutputData(), $i->getPrimaryId());
            if ($i->onBreak()) break;

            if ($primary_id = $i->getPrimaryId()) :
                $types = array_diff($i->getTypes(), ['data']);

                foreach ($types as $type) :
                    $Type = ucfirst($type);

                    // Filtrage des données de sorties pour le type courant.
                    $i->_outputTypeFilter($type);
                    if ($i->onBreak()) break2;

                    // Vérification des données de sorties pour le type courant.
                    $i->_outputTypeCheck($type);
                    if ($i->onBreak()) break2;

                    // Récupération de la liste des données pour le type courant.
                    $datas = call_user_func([$i, "getOutput{$Type}"]);

                    // Evénement pré-insertion des données de sorties pour le type courant.
                    call_user_func_array([$i, "insert{$Type}Before"], [$datas, $primary_id]);
                    if ($i->onBreak()) break2;

                    // Insertion des données de sorties pour le type courant.
                    foreach ($datas as $key => $value) :
                        call_user_func_array([$i, "insert{$Type}"], [$key, $value, $primary_id]);
                        if ($i->onBreak()) break3;
                    endforeach;

                    // Evénement post-insertion des données de sorties pour le type courant.
                    call_user_func_array([$i, "insert{$Type}After"], [$datas, $primary_id]);
                    if ($i->onBreak()) break2;
                endforeach;

            endif;

            // Evénement post-insertion.
            $i->after($i->getPrimaryId());
            if ($i->onBreak()) break;

            $i->setOnBreak();
        endwhile;

        var_dump($i->getResults());

        return $i->getResults();
    }

    /**
     * {@inheritdoc}
     */
    public function after($primary_id = null)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function before($primary_id = null)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function boot()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function getInput($key = null, $default = null)
    {
        if (is_null($key)) :
            return $this->input;
        else :
            return Arr::get($this->input, $key, $default);
        endif;
    }

    /**
     * {@inheritdoc}
     */
    public function getMap($type)
    {
        return Arr::get($this->map, $type, []);
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputData($key = null, $default = null)
    {
        if (is_null($key)) :
            return Arr::get($this->output, 'data', $default);
        else :
            return Arr::get($this->output, "data.{$key}", $default);
        endif;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputMeta($meta_key = null, $default = null)
    {
        if (is_null($meta_key)) :
            return Arr::get($this->output, 'meta', $default);
        else :
            return Arr::get($this->output, "meta.{$meta_key}", $default);
        endif;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryId()
    {
        return $this->primaryId;
    }

    /**
     * {@inheritdoc}
     */
    public function getResults()
    {
        $res = [];
        $res['insert_id'] = $this->getPrimaryId();
        $res['success'] = $this->isSuccessfully();
        foreach ($this->notices()->getTypes() as $type) :
            if ($messages = $this->notices()->getMessages($type)) :
                $res['notices'][$type] = $messages;
            endif;
        endforeach;

        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes()
    {
        return array_intersect($this->types, ['data', 'meta']);
    }

    /**
     * {@inheritdoc}
     */
    public function insertData($datas = [], $primary_id = null)
    {
        return $this->notices()->add(
            'error',
            __('Méthode d\'enregistrement des données d\'import non définie', 'tify')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function insertDataAfter($datas = [], $primary_id = null)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function insertDataBefore($datas = [], $primary_id = null)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function insertMeta($meta_key, $meta_value, $primary_id = null)
    {
        return $this->notices()->add(
            'warning',
            __('Méthode d\'enregistrement des metadonnées d\'import non définie', 'tify')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function insertMetaAfter($metas = [], $primary_id = null)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function insertMetaBefore($metas = [], $primary_id = null)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessfully()
    {
        return $this->success !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function mapData()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function mapMeta()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function notices()
    {
        if ($this->notices instanceof NoticesInterface) :
            return $this->notices;
        else :
            return $this->notices = app('notices');
        endif;
    }

    /**
     * {@inheritdoc}
     */
    public function parseInput($input)
    {
        return $input;
    }

    /**
     * {@inheritdoc}
     */
    public function onBreak()
    {
        return $this->break === true;
    }

    /**
     * {@inheritdoc}
     */
    public function outputCheckData($key, $value = null, $primary_id = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function outputCheckMeta($meta_key, $meta_value = null, $primary_id = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function outputFilterData($key, $value = null, $primary_id = null)
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function outputFilterMeta($meta_key, $meta_value = null, $primary_id = null)
    {
        return $meta_value;
    }

    /**
     * {@inheritdoc}
     */
    public function outputSetData($key, $raw_value = null)
    {
        return $raw_value;
    }

    /**
     * {@inheritdoc}
     */
    public function outputSetMeta($meta_key, $raw_meta_value = null)
    {
        return $raw_meta_value;
    }

    /**
     * {@inheritdoc}
     */
    public function setOnBreak()
    {
        $this->break =  true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPrimaryId($primary_id)
    {
        $this->primaryId = $primary_id;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setSuccess($success = true)
    {
        $this->success = $success;

        return $this;
    }
}