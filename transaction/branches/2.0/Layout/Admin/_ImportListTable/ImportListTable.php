<?php

namespace tiFy\Components\AdminView\ImportListTable;

use tiFy\Field\Field;
use tiFy\Components\AdminView\AjaxListTable\AjaxListTable;

class ImportListTable extends AjaxListTable
{
    /**
     * Classe de l'importateur de données
     * @var  string
     */
    protected $Importer             = null;
    
    /**
     * Table de correspondance des données array( 'column_id' => 'input_key' )
     * Tableau dimensionné ['column_id1' => 'input_key1', 'column_id2' => 'input_key2', ...]
     * @var array
     */ 
    protected $MapColumns           = [];

    /**
     * Champs d'options du formulaire d'import
     * @var \tiFy\Core\Field\Factory[]
     */
    protected $OptionsFields        = [];

    /**
     * PARAMETRAGE
     */
    /** 
     * Définition de la cartographie des paramètres autorisés
     */
    public function set_params_map()
    {
        $params = parent::set_params_map();
        array_push($params, 'Importer', 'MapColumns', 'OptionsFields');
        
        return $params;
    }
    
    /**
     * Définition de la classe de l'importateur de données (désactive l'import)
     */ 
    public function set_importer()
    {
        return false;
    }  
    
    /**
     * Définition de la table de correspondance des données entre l'identifiant de colonnes et la clé des données d'import
     * ex: array( [column_id] => [input_key] );
     */
    public function set_columns_map()
    {
        return [];
    }

    /**
     * Définition des champs d'options du formulaire d'import
     */
    public function set_options_fields()
    {
        return [];
    }
    
    /**
     * Initialisation du délimiteur du fichier d'import
     */
    public function initParamImporter()
    {               
        return $this->Importer = $this->set_importer();
    } 
    
    /**
     * Paramétrage de la table de correspondance des données entre l'identifiant de colonnes et la clé des données d'import
     */
    public function initParamMapColumns()
    {
        if ($column_map = $this->set_columns_map()) :
            $this->MapColumns = (array)$column_map;
        else :
            $this->MapColumns = (array)$this->getConfig('columns_map');
        endif;
    }

    /**
     * Initialisation des champs d'options du formulaire d'import
     */
    public function initParamOptionsFields()
    {
        $options_fields = $this->set_options_fields();
        if (! empty($options_fields)) :
            $this->OptionsFields = (array)$options_fields;
        else :
            $this->OptionsFields = $this->getConfig('options_fields');
        endif;
    }
    
    /**
     * Données passées dans la requête d'import Ajax 
     */
    public function getAjaxImportData()
    {
        return [];
    }    
    
    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de l'interface d'administration privée
     * {@inheritDoc}
     * @see \tiFy\Core\Templates\Admin\Model\AjaxListTable::_admin_init()
     */
    public function _admin_init()
    {
        parent::_admin_init();

        add_action( 'wp_ajax_'. $this->template()->getID() .'_'. self::classShortName() .'_import', array( $this, 'wp_ajax_import' ) );    
    }
    
    /**
     * Mise en file des scripts de l'interface d'administration
     * {@inheritDoc}
     * @see \tiFy\Core\Templates\Admin\Model\AjaxListTable::_admin_enqueue_scripts()
     */
    public function _admin_enqueue_scripts()
    {
        parent::_admin_enqueue_scripts();
        
        tify_control_enqueue( 'progress' );

        // Chargement des scripts
        wp_enqueue_style( 'tiFyTemplatesAdminImport', self::tFyAppAssetsUrl('Import.css', get_class()), array( ), 150607 );
        wp_enqueue_script( 'tiFyTemplatesAdminImport', self::tFyAppAssetsUrl('Import.js', get_class()), array( 'jquery' ), 150607 );
        wp_localize_script( 
            'tiFyTemplatesAdminImport', 
            'tiFyTemplatesAdminImport',
            array(
                'prepare'   => __( 'Préparation de l\'import ...', 'tify' ),
                'cancel'    => __( 'Annulation en cours ...', 'tify' ),
                'notices'   => [
                    'error'     => [
                        'color'     => '#DC3232',
                        'title'     => __('Erreurs', 'tify')
                    ],
                    'warning'   => [
                        'color'     => '#FFB900',
                        'title'     => __('Avertissements', 'tify')
                    ],
                    'info'      => [
                        'color'     => '#00A0D2',
                        'title'     => __('Informations', 'tify')
                    ],
                    'success'   => [
                        'color'     => '#46B450',
                        'title'     => __('Succès', 'tify')
                    ]
                ]
            )
        );
    } 
            
    /**
     * Traitement Ajax de l'import des données
     */
    public function wp_ajax_import()
    {
        // Initialisation des paramètres de configuration de la table
        $this->initParams();

        // Bypass
        if (! $this->Importer) :
            return;
        endif;

        if ($input_data = $this->getResponse()) :
            if (isset($_REQUEST['__import_options'])) :
                parse_str($_REQUEST['__import_options'], $attrs);
            else :
                $attrs = [];
            endif;
            $res = call_user_func($this->Importer . '::import', (array)current($input_data), $attrs);
        else :
            $res = [
                'insert_id' => 0,
                'success'   => false,
                'notices'   => [
                    'error' => [
                        'tiFyTemplatesAdminImport-UnavailableContent' => [
                            'message' => __( 'Le contenu à importer est indisponible', 'tify')
                        ]
                    ]
                ]
            ];
        endif;

        wp_send_json($res);
        exit;
    }

    /**
     * TRAITEMENT
     */
    /**
     * Vérification d'existance d'un élément
     * @param obj $item données de l'élément
     * 
     * @return bool false l'élément n'existe pas en base | true l'élément existe en base
     */
    public function item_exists($item)
    {
        if (!$this->ItemIndex) :
            return false;
        endif;

        if (isset($this->ImportMap[$this->ItemIndex])) :
            $index = $this->ImportMap[$this->ItemIndex];
        else :
            $index = $this->ItemIndex;
        endif;

        if (!isset($item->{$index})) :
            return false;
        endif;

        return $this->db()->select()->has($this->ItemIndex, $item->{$item->{$index}});
    }
    
    /**
     * RECUPERATION DE PARAMETRE
     */
    /**
     * Récupération des colonnes de la table
     */
    public function get_columns() 
    {
        if( $this->Importer ) :
            return array( '_tiFyTemplatesImport_col_action' => __( 'Action', 'tify' ) ) + $this->Columns;
        else :
            return $this->Columns;
        endif;
    }    
    
    /**
     * AFFICHAGE
     */
    /**
     * @param string $which
     */
    protected function extra_tablenav( $which ) 
    {
        parent::extra_tablenav( $which );
?>           
<button type="submit" class="button-primary tiFyTemplatesImport-submit" data-id="<?php echo $this->template()->getID() .'_'. self::classShortName();?>">
    <span class="dashicons dashicons-admin-generic" style="vertical-align:middle;line-height:18px;"></span>
    <?php _e( 'Lancer l\'import', 'tify' );?>
</button>
<?php
        if( $which === 'top' ) :
            tify_control_progress(
                array(
                    'id'        => 'tiFyTemplatesImport-ProgressBar',
                    'title'     => __( 'Progression de l\'import', 'tify' ),
                    'value'     => 0
                )
            );
        endif;
    }

    /**
     * AFFICHAGE
     */
    /**
     * Vues
     */
    public function views()
    {
        if ($this->OptionsFields) :
            $this->import_form_options();
        endif;

        parent::views();
    }

    /**
     *
     */
    public function import_form_options()
    {
?>
<div class="tiFyTemplatesImport-options">
    <strong class="tiFyTemplatesImport-optionsLabel"><?php _e( 'Options :', 'tify' );?></strong>
    <form name="tiFyTemplatesImport-optionsForm" class="tiFyTemplatesFileImport-optionsForm">
        <table class="form-table">
            <tbody>
                <?php foreach ($this->OptionsFields as $option_field) : ?>
                <tr>
                    <th><?php echo $option_field['label'];?></th>
                    <td>
                        <?php echo Field::{$option_field['type']}($option_field['attrs']);?>
                    </td>
                </tr>
                <?php endforeach;?>
            </tbody>
        </table>
    </form>
</div>
<?php
    }

    /**
     * Champs cachés
     */
    public function hidden_fields()
    {        
        parent::hidden_fields();
        
        $data = wp_parse_args( 
            $this->getAjaxImportData(),
            array(
                'action'    => $this->template()->getID() .'_'. self::classShortName() .'_import'
            )
        );        
?><input type="hidden" id="ajaxImportData" value="<?php echo rawurlencode( json_encode( $data ) );?>" /><?php
    }

    /**
     * Colonne de traitement des actions
     */
    public function column__tiFyTemplatesImport_col_action( $item )
    {
        $output = "";

        $output .=  "<a href=\"#\" class=\"tiFyTemplatesImport-RowImport\" data-item_index_key=\"{$this->ItemIndex}\" data-item_index_value=\"". ( isset( $item->{$this->ItemIndex} ) ? $item->{$this->ItemIndex} : 0 ) ."\" >".
            "<span class=\"dashicons dashicons-admin-generic tiFyTemplatesImport-RowImportIcon\"></span>".
            "</a>";
        $output .= ( $this->item_exists( $item ) ) ? "<span class=\"dashicons dashicons-paperclip tiFyTemplatesImport-ExistItem\"></span>" : "";
        $output .= "<span class=\"dashicons dashicons-yes tiFyTemplatesImport-Result tiFyTemplatesImport-Result--success\"></span>";
        $output .= "<span class=\"dashicons dashicons-no tiFyTemplatesImport-Result tiFyTemplatesImport-Result--error\"></span>";
        $output .= "<div class=\"tiFyTemplatesImport-Notices\"></div>";

        return $output;
    }
}