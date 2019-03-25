<?php
namespace tiFy\Core\Templates\Admin\Model\CsvImport;

use tiFy\Lib\Csv\Csv;
use tiFy\Core\Templates\Admin\Model\FileImport\FileImport;

class CsvImport extends FileImport
{
    /**
     * Délimiteur de colonnes du fichier CSV.
     * @var string
     */
    protected $Delimiter        = ',';

    /**
     * Définie si le fichier contient une entête.
     * @var bool
     */
    protected $HasHeader        = false;

    /**
     * PARAMETRAGE
     */    
    /** 
     * Définition de la cartographie des paramètres autorisés
     *
     * @return array
     */
    public function set_params_map()
    {
        $params = parent::set_params_map();
        array_push($params, 'Delimiter', 'HasHeader');

        return $params;
    }

    /**
     * Définition du délimiteur de colonnes du fichier d'import.
     *
     * @return string
     */
    public function set_delimiter()
    {
        return ',';
    }

    /**
     * Définition si le fichier contient une entête.
     *
     * @return bool
     */
    public function set_has_header()
    {
        return false;
    }

    /**
     * Définition des champs d'options du formulaire d'import
     */
    public function set_options_fields()
    {
        return [
            [
                'label' => __('Le fichier d\'import comporte une entête', 'tify'),
                'type'  => 'checkbox',
                'attrs' => [
                    'name'      => 'has_header',
                    'value'     => 'on'
                ]
            ]
        ];
    }

    /**
     * Initialisation du délimiteur du fichier d'import.
     *
     * @return string
     */
    public function initParamDelimiter()
    {               
        return $this->Delimiter = $this->set_delimiter();
    }

    /**
     * Initialisation de l'entête du fichier d'import.
     *
     * @return string
     */
    public function initParamHasHeader()
    {
        return $this->HasHeader = $this->set_has_header();
    }

    /**
     * TRAITEMENT
     */
    /**
     * Récupération de la réponse
     *
     * @return object[]
     */
    protected function getResponse()
    {
        $params = $this->parse_query_args();

        if (empty( $params['filename'])) :
            return;
        endif;

        // Attributs de récupération des données CSV
        if ($this->current_item()) :
            $attrs = array(
                'filename'      => $params['filename'],
                'columns'       => $this->FileColumns,
                'delimiter'     => $this->Delimiter,
                'has_header'    => $this->HasHeader
            );
            $Csv = Csv::get(current($this->current_item()), $attrs);
        else :
            $attrs = array(
                'filename'      => $params['filename'],
                'columns'       => $this->FileColumns,
                'delimiter'     => $this->Delimiter,
                'query_args'    => array(
                    'paged'         => isset( $params['paged'] ) ? (int) $params['paged'] : 1,
                    'per_page'      => $this->PerPage
                ),
                'has_header'    => $this->HasHeader
            );
            
            /// Trie
            if (! empty($params['orderby'])) :
                $attrs['orderby'] = $params['orderby'];
            endif;
            
            /// Recherche
            if (! empty($params['search'])) :
                $attrs['search'] = array(
                    array(
                        'term'      => $params['search']
                    )
                );
            endif;

            // Traitement du fichier d'import
            $Csv = Csv::getList( $attrs );
        endif;
                
        $items = array();

        foreach($Csv->toArray() as $import_index => $item) :
            $item['_import_row_index'] = $import_index;
            $items[] = (object) $item;
        endforeach;
                
        $this->TotalItems = $Csv->getTotalItems();
        $this->TotalPages = $Csv->getTotalPages();

        return $items;
    }
}
