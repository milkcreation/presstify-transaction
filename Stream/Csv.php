<?php

namespace tiFy\Plugins\Transaction\Stream\Csv;

use League\Csv\CharsetConverter;
use League\Csv\Exception;
use League\Csv\Reader;
use League\Csv\ResultSet;
use League\Csv\Statement;

/**
 *  USAGE :
 *
 * $Csv = \tiFy\Lib\Csv\Csv::getList(
 *   [
 *      'filename'      => ABSPATH .'/example.csv'
 *      'delimiter'     => ';',
 *      'query_args'    => [
 *          'paged'         => 1,
 *          'per_page'      => -1
 *      ],
 *      'columns'       => ['lastname', 'firstname', 'email'],
 *      'orderby'       => [
 *          'lastname'      => 'ASC'
 *      ],
 *      'search'        => [
 *          [
 *              'term'      => '@domain.ltd',
 *              'cols'      => ['email']
 *          ],
 *          [
 *              'term'      => 'john',
 *              'cols'      => []
 *          ],
 *      ]
 *   ]
 * );
 *
 * // Récupération la liste des éléments sous forme d'iterateur
 * // @var ResultSet
 * $items = $Csv->getItems();
 *
 * // Récupération la liste des éléments sous forme de tableau
 * // @var ResultSet
 * $items = $Csv->toArray();
 *
 * // Nombre total de resultats
 * // @var int
 * $total_items = $Csv->getTotalItems();
 *
 * // Nombre de résultats
 * // @var int
 * $total_pages = $Csv->getTotalPages();
 *
 * // Nombre d'éléments trouvés
 * // @var int
 * $found_items = $Csv->getFoundItems();
 */

class Csv
{
    /**
     * Chemin vers le fichier de données à traiter.
     * @var string
     */
    protected $filename = '';

    /**
     * Le fichier à traité contient une entête.
     */
    protected $hasHeader = false;

    /**
     * Propriétés de traitement fichier CSV.
     * @var array
     */
    protected $properties = [
        /// Délimiteur de champs
        'delimiter' => ',',
        /// Caractère d'encadrement 
        'enclosure' => '"',
        /// Caractère de protection
        'escape'    => '\\'
    ];

    /**
     * Arguments de requête de récupération des éléments.
     * @var array
     */
    protected $queryArgs = [
        // Page courante
        'paged'    => 1,
        // Nombre d'éléments par page
        'per_page' => -1
    ];

    /**
     * La ligne de démarrage du traitement.
     * @var int
     */
    protected $offset = 0;

    /**
     * Cartographie des colonnes.
     * ex ['firstname', 'lastname', 'email'];
     * @var string[]
     */
    protected $columns = [];

    /**
     * Nombre d'éléments trouvés.
     * @var int
     */
    protected $foundItems = 0;

    /**
     * Nombre d'éléments total.
     * @var int
     */
    protected $totalItems = 0;

    /**
     * Nombre de page total.
     * @var int
     */
    protected $totalPages = 0;

    /**
     * Liste des éléments.
     * @var null|ResultSet
     */
    protected $items;

    /**
     * Arguments de trie.
     * @var array
     */
    public $orderBy = [];

    /**
     * Arguments de recherche.
     * @var array
     */
    public $searchArgs = [];

    /**
     * Types de fichier autorisés.
     * @todo
     * @var string[]
     */
    protected $allowedMimeType = ['csv', 'txt'];

    /**
     * Trie des données.
     * @var array
     */
    protected $sorts = [];

    /**
     * Filtres de données.
     * @var array
     */
    protected $filters = [];

    /**
     * Relation de filtrage des données.
     * @var string
     */
    protected $filtersRelation = 'OR';

    /**
     * Encodage des résultats.
     * @var array {
     *      @var string $from - Encodage d'entrée
     *      @var string $to - Encodage de sortie
     * }
     */
    protected $charset_conv = [];

    /**
     * CONSTRUCTEUR.
     *
     * @param array $options Liste des attributs de configuration.
     *
     * @return void
     */
    public function __construct($options = [])
    {
        foreach ($options as $option_name => $option_value) :
            switch ($option_name) :
                case 'filename' :
                    $this->setFilename($option_value);
                    break;
                case 'has_header' :
                    $this->hasHeader = (bool)$option_value;
                    break;
                case 'offset' :
                    $this->offset = (int)$option_value;
                    break;
                case 'delimiter' :
                case 'enclosure' :
                case 'escape' :
                    $this->setProperty($option_name, $option_value);
                    break;
                case 'query_args' :
                    foreach ($option_value as $query_arg => $value) :
                        $this->setQueryArg($query_arg, $value);
                    endforeach;
                    break;
                case 'columns' :
                    $this->columns = $option_value;
                    break;
                case 'orderby' :
                    $this->orderBy = $option_value;
                    break;
                case 'search' :
                    $this->searchArgs = $option_value;
                    break;
                case 'charset_conv' :
                    $this->setCharsetConv($option_value);
                    break;
            endswitch;
        endforeach;
    }

    /**
     * Récupération d'éléments.
     *
     * @param array $options Liste des attributs de configuration.
     *
     * @return self
     */
    final public static function getList($options = [])
    {
        $csv = new static($options);
        $csv->getRows();

        return $csv;
    }

    /**
     * Récupération d'une ligne de donnée du fichier.
     *
     * @param int $offset Ligne de l'élément à récuperer.
     * @param array $options Liste des attributs de configuration.
     *
     * @return self
     */
    final public static function get($offset = 0, $options = [])
    {
        $options['offset'] = $offset;
        $options['query_args']['per_page'] = 1;

        $csv = new static($options);
        $csv->getRows();

        return $csv;
    }

    /**
     * Définition du fichier de données.
     *
     * @param string $filename Chemin absolu vers le fichier de données à traiter.
     *
     * @return self
     */
    final public function setFilename($filename)
    {
        if (file_exists($filename)) :
            $this->filename = $filename;
        endif;

        return $this;
    }

    /**
     * Définition de propriété Csv.
     *
     * @param string $key Identifiant de qualification de la propriété. delimiter|enclosure|escape.
     * @param mixed $value Valeur de définition de la propriété.
     *
     * @return self
     */
    final public function setProperty($key, $value = '')
    {
        if (!in_array($key, ['delimiter', 'enclosure', 'escape'])) :
            return $this;
        endif;

        $this->properties[$key] = $value;

        return $this;
    }

    /**
     * Définition d'un argument de requête de récupération des élément du fichier.
     *
     * @param string $key Identifiant de qualification de l'argument de requête.
     * @param mixed $value Valeur de l'argument de requête.
     *
     * @return self
     */
    final public function setQueryArg($key, $value = '')
    {
        $this->queryArgs[$key] = $value;

        return $this;
    }

    /**
     * Définition du nombre d'éléments trouvés.
     *
     * @param int $found_items Nombre d'éléments trouvés à définir.
     *
     * @return self
     */
    final public function setFoundItems($found_items)
    {
        $this->foundItems = (int)$found_items;

        return $this;
    }

    /**
     * Définition du nombre total d'éléments.
     *
     * @param int $found_items Nombre d'éléments total à définir.
     *
     * @return self
     */
    final public function setTotalItems($total_items)
    {
        $this->totalItems = (int)$total_items;

        return $this;
    }

    /**
     * Définition du nombre total de page d'éléments.
     *
     * @param int $found_items de pages totales à définir.
     *
     * @return self
     */
    final public function setTotalPages($total_pages)
    {
        $this->totalPages = (int)$total_pages;

        return $this;
    }

    /**
     * Définition de l'encodage des résultats.
     *
     * @param array $charset_conv {
     *      @var string $from - Encode d'entrée.
     *      @var string $to - Encodage de sortie.
     * }
     * @return $this
     */
    final public function setCharsetConv($charset_conv = [])
    {
        if (is_array($charset_conv) && !empty($charset_conv['from']) && !empty($charset_conv['to'])) :
            $this->charset_conv = $charset_conv;
        endif;

        return $this;
    }

    /**
     * Récupération du fichier de données.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Récupération de propriété Csv.
     *
     * @param string $key Identifiant de qualification de la propriété. delimiter|enclosure|escape.
     * @param mixed $default Valeur de retour par défaut.
     *
     * @return mixed
     */
    public function getProperty($key, $default = '')
    {
        // Bypass
        if (!in_array($key, ['delimiter', 'enclosure', 'escape'])) :
            return $default;
        endif;

        if (isset($this->properties[$key])) :
            return $this->properties[$key];
        endif;

        return $default;
    }

    /**
     * Récupération d'un argument de requête.
     *
     * @param string $key Identifiant de qualification de l'argument de requête.
     * @param mixed $default Valeur de retour par défaut.
     *
     * @return mixed
     */
    public function getQueryArg($key, $default = '')
    {
        if (isset($this->queryArgs[$key])) :
            return $this->queryArgs[$key];
        endif;

        return $default;
    }

    /**
     * Récupération du nombre d'éléments trouvés.
     *
     * @return int
     */
    public function getFoundItems()
    {
        return (int)$this->foundItems;
    }

    /**
     * Récupération du nombre total d'éléments.
     *
     * @return int
     */
    public function getTotalItems()
    {
        return (int)$this->totalItems;
    }

    /**
     * Récupération du nombre total de page.
     *
     * @return int
     */
    public function getTotalPages()
    {
        return (int)$this->totalPages;
    }

    /**
     * Récupération de la cartographie des colonnes.
     *
     * @return string[]
     */
    public function getColumns()
    {
        return ! empty($this->columns) ? $this->columns : [];
    }

    /**
     * Récupération de l'index d'une colonne.
     *
     * @return null|int
     */
    final public function getColumnIndex($column)
    {
        $index = array_search($column, $this->columns);

        if ($index!== false) :
            return (int)$index;
        endif;

        return null;
    }

    /**
     * Récupération de l'intitulé d'une colonne en fonction de son index.
     *
     * @param int $index Index de la colonne.
     *
     * @return null|string
     */
    final public function getColumnFromIndex($index = 0)
    {
        $_index = array_search($index, array_keys($this->columns));

        if ($_index!== false) :
            return $this->columns[$_index];
        endif;

        return null;
    }

    /**
     * Récupération de la valeur d'une colonne en fonction de son index.
     *
     * @param array $row Ligne du CSV.
     * @param int $index Index de colonne souhaité.
     *
     * @return mixed
     */
    final public function getColumnValue($row = [], $index = 0)
    {
        return isset($row[$index]) ? $row[$index] : (isset($row[$this->getColumnFromIndex($index)]) ? $row[$this->getColumnFromIndex($index)] : null);
    }

    /**
     * Récupération des éléments.
     *
     * @return null|ResultSet
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Récupération de la liste des éléments sous forme de tableau.
     *
     * @return array
     */
    public function toArray()
    {
        return ($this->items instanceof ResultSet) ? \iterator_to_array($this->items) : [];
    }

    /**
     * Récupération de la liste des ligne du fichier.
     *
     * @return array
     */
    public function getRows()
    {
        /**
         * Traitement global du fichier csv
         * @var Reader $reader
         */
        $reader = Reader::createFromPath($this->getFilename(), 'r');

        // Définition d'entête dans le fichier CSV
        if ($this->hasHeader) :
            $reader->setHeaderOffset(0);
        endif;

        // Définition des propriétés du fichier CSV
        // Définition du délimiteur
        try {
            $reader->setDelimiter($this->getProperty('delimiter', ','));
        } catch(Exception $e) {
            exit;
        }
        // Définition de l'encapsulation
        try {
            $reader->setEnclosure($this->getProperty('enclosure', '"'));
        } catch(Exception $e) {
            exit;
        }
        // Définition de l'encapsulation du caractère d'échapement
        try {
            $reader->setEscape($this->getProperty('escape', '\\'));
        } catch(Exception $e) {
            exit;
        }

        // Conversion de l'encodage des résultats
        if ($this->charset_conv) :
            CharsetConverter::addTo($reader, $this->charset_conv['from'], $this->charset_conv['to']);
        endif;

        $stmt = new Statement();

        // Filtrage
        if ($this->setFilters($stmt->process($reader))) :
            $stmt = $stmt->where([$this, 'searchFilterCallback']);
        endif;

        // Trie des éléments
        if ($this->setSorts()) :
            $stmt = $stmt->orderBy([$this, 'searchSortCallback']);
        endif;

        // Définition du nombre total de résultats
        $total_records = $stmt->process($reader);

        $total_items = count($total_records);
        $this->setTotalItems($total_items);

        // Définition des attributs de pagination
        $per_page = $this->getQueryArg('per_page', -1);
        $paged = $this->getQueryArg('paged', 1);
        $offset = $this->offset ? : (($per_page > -1) ? (($paged - 1) * $per_page) : 0);
        $total_pages = ($per_page > -1) ? ceil($total_items / $per_page) : 1;
        $this->setTotalPages($total_pages);

        // Définition de la ligne de démarrage du traitement
        try {
            $stmt = $stmt->offset((int)$offset);
        } catch(Exception $e) {
            exit;
        }
        // Définition du nombre d'éléments à traiter
        try {
            $stmt = $stmt->limit((int)$per_page);
        } catch(Exception $e) {
            exit;
        }
        $records = $stmt->process($reader, $this->getColumns());

        // Définition du nombre d'élément trouvés pour la requête
        $this->setFoundItems(count($records));

        $this->items = $records;

        return $this->toArray();
    }

    /**
     * Définition de l'ordre de trie.
     *
     * @return mixed
     */
    final public function setSorts()
    {
        if (!$this->orderBy)
            return null;

        foreach ((array)$this->orderBy as $key => $value) :
            $key = (is_numeric($key)) ? $key : $this->getColumnIndex($key);
            if (!is_numeric($key))
                continue;
            $this->sorts[$key] = in_array(strtoupper($value), ['ASC', 'DESC']) ? strtoupper($value) : 'ASC';
        endforeach;

        return $this->sorts;
    }

    /**
     * Méthode de rappel de trie des données.
     *
     * @return int
     */
    final public function searchSortCallback($rowA, $rowB)
    {
        foreach ($this->sorts as $col => $order) :
            switch ($order) :
                case 'ASC' :
                    return strcasecmp($this->getColumnValue($rowA, $col), $this->getColumnValue($rowB, $col));
                    break;
                case 'DESC' :
                    return strcasecmp($this->getColumnValue($rowB, $col), $this->getColumnValue($rowA, $col));
                    break;
            endswitch;
        endforeach;
    }

    /**
     * Définition du filtrage.
     *
     * @return mixed
     */
    final public function setFilters($csvObj)
    {
        if (!$this->searchArgs)
            return null;

        $clone = clone $csvObj;
        $count = count($clone->fetchOne());

        foreach ($this->searchArgs as $key => $f) :
            if (!is_numeric($key) && ($key === 'relation') && (in_array(strtoupper($f), ['OR', 'AND']))) :
                $this->filtersRelation = strtoupper($f);
            endif;
            if (empty($f['term']))
                continue;
            $term = $f['term'];

            $exact = !empty($f['exact']) ? true : false;

            if (empty($f['columns'])) :
                $columns = range(0, ($count - 1), 1);
            elseif (is_string($f['columns'])) :
                $columns = array_map('trim', explode(',', $f['columns']));
            elseif (is_array($f['columns'])) :
                $columns = $f['columns'];
            endif;
            $filters = [];
            foreach ($columns as $c) :
                if (!is_numeric($c)) :
                    $c = $this->getColumnIndex($c);
                endif;

                $this->filters[] = [
                    'col'   => (int)$c,
                    'exact' => $exact,
                    'term'  => $term
                ];
            endforeach;
        endforeach;

        return $this->filters;
    }

    /**
     * Méthode de rappel du filtrage.
     *
     * @return bool
     */
    final public function searchFilterCallback(array $record)
    {
        $has = [];

        foreach ($this->filters as $f) :
            $regex = $f['exact'] ? '^' . $f['term'] . '$' : $f['term'];
            if (preg_match('/' . $regex . '/i', $this->getColumnValue($record, $f['col']))) :
                $has[$f['col']] = 1;
            else :
                $has[$f['col']] = 0;
            endif;
        endforeach;

        switch ($this->filtersRelation) :
            default :
            case 'OR' :
                if (in_array(1, $has))
                    return true;
                break;
            case 'AND' :
                if (!in_array(0, $has))
                    return true;
                break;
        endswitch;

        return false;
    }
}