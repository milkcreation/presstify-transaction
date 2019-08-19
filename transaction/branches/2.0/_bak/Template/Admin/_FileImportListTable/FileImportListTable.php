<?php

namespace tiFy\Components\AdminView\FileImportListTable;

use tiFy\Components\AdminView\ImportListTable\ImportListTable;

class FileImportListTable extends ImportListTable
{
    /**
     * Fichier d'import interne
     * @var string
     */
    protected $Filename         = '';

    /**
     * Colonnes du fichier d'import
     * @var array
     */
    protected $FileColumns      = [];

    /**
     * Habilitation de téléchargement de fichier externe
     * @var bool
     */
    protected $Uploadable       = true;

    /**
     * Répertoire d'upload
     * @var string
     */
    protected $UploadDir        = '';

    /**
     * Activation du déboguage de l'import
     * @var bool|int (false: désactivé, true: activé sur le premier élément, int: numéro de ligne de l'élément à traité)
     */
    protected $ImportDebug      = false;

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
        array_push($params, 'Filename', 'FileColumns', 'Uploadable', 'UploadDir', 'ImportDebug');
        
        return $params;
    }

    /**
     * Définition de la clé primaire d'un élément
     *
     * @return string
     */
    public function set_item_index()
    {
        return '_import_row_index';
    }

    /**
     * Définition du fichier d'import interne
     *
     * @return string
     */
    public function set_filename()
    {
        return '';
    }

    /**
     * Définition des colonnes du fichier d'import
     *
     * @return array
     */
    public function set_file_columns()
    {
        return [];
    }

    /**
     * Définition si l'utilisateur est habilité à télécharger un fichier externe
     *
     * @return bool
     */
    public function set_uploadable()
    {
        return true;
    }

    /**
     * Définition du repertoire d'upload
     *
     * @return string
     */
    public function set_upload_dir()
    {
        return '';
    }

    /**
     * Initialisation du fichier d'import externe
     *
     * @return string
     */
    public function initParamFilename()
    {
        return $this->Filename = $this->set_filename();
    }

    /**
     * Initialisation des colonnes du fichier d'import
     *
     * @return array
     */
    public function initParamFileColumns()
    {
        return $this->FileColumns = $this->set_file_columns();
    }

    /**
     * Initialisation de l'habilitation à télécharger un fichier externe
     *
     * @return bool
     */
    public function initParamUploadable()
    {
        return $this->Uploadable = $this->set_uploadable();
    }

    /**
     * Initialisation du répertoire d'upload
     *
     * @return string
     */
    public function initParamUploadDir()
    {
        if ($this->UploadDir = $this->set_upload_dir()) :
        else :
            $upload_dir = wp_upload_dir();
            $this->UploadDir = $upload_dir['basedir'];
        endif;

        return $this->UploadDir;
    }

    /**
     * Données passées dans la requête de récupération Ajax de Datatables
     *
     * @return array
     */
    public function getAjaxDatatablesData()
    {
        return wp_parse_args(
            array(
                'filename' => $this->QueryArgs['filename']
            ),
            parent::getAjaxDatatablesData()
        );
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de l'interface d'administration privée
     * {@inheritDoc}
     * @see \tiFy\Core\Templates\Admin\Model\AjaxListTable::_admin_init()
     */
    public function admin_init()
    {
        parent::admin_init();

        // Actions Ajax
        add_action('wp_ajax_' . $this->template()->getID() . '_' . self::classShortName() . '_fileimport_upload', [$this, 'wp_ajax_upload']);
    }
    
    /**
     * Mise en file des scripts de l'interface d'administration
     * {@inheritDoc}
     * @see \tiFy\Core\Templates\Admin\Model\AjaxListTable::_admin_enqueue_scripts()
     */
    public function admin_enqueue_scripts()
    {
        parent::admin_enqueue_scripts();

        // Chargement des scripts
        wp_enqueue_style('tiFyTemplatesAdminFileImport', self::tFyAppAssetsUrl('FileImport.css', get_class()), [], 150607);
        wp_enqueue_script('tiFyTemplatesAdminFileImport', self::tFyAppAssetsUrl('FileImport.js', get_class()), ['jquery'], 150607);
    }

    /**
     * Affichage la page courante.
     *
     * @return void
     */
    public function current_screen($current_screen = null)
    {
        if ($this->ImportDebug !== false) :
            $row_import = ($this->ImportDebug === true) ? 0 : (int) $this->ImportDebug;
        endif;

        if (isset($row_import)) :
            $_REQUEST['_import_row_index'] = $row_import;
        endif;

        parent::current_screen($current_screen);
        
        // DEBUG - Tester la fonctionnalité d'import > Décommenter $_REQUEST['_import_row_index'] et commenter le return (ligne suivante)
        if (!isset($row_import)) :
            return;
        endif;

        if (!$this->Importer) :
            return;
        endif;

        if (isset($this->items[$_REQUEST['_import_row_index']])) :
            $res = call_user_func($this->Importer . '::import', (array)$this->items[$_REQUEST['_import_row_index']]);
        elseif ($this->items) :
            $res = call_user_func($this->Importer . '::import', (array)current($this->items));
        else :
            $res = [
                'insert_id' => 0,
                'success'   => false,
                'notices'   => [
                    'error' => [
                        'tiFyTemplatesAdminImport-UnavailableContent' => [
                            'message'   => __('Le contenu à importer est indisponible', 'tify')
                        ]
                    ]
                ]
            ];
        endif;

        wp_send_json($res);
    }
    
    /**
     * Traitement Ajax de téléchargement du fichier d'import
     *
     * @return string
     */
    public function wp_ajax_upload()
    {
        // Initialisation des paramètres de configuration de la table
        $this->initParams();

        // Récupération des variables de requête
        $file = current($_FILES);
        $filename = sanitize_file_name(basename($file['name']));

        $response = [];
        if (!@ move_uploaded_file($file['tmp_name'], $this->UploadDir . "/" . $filename)) :
            $response = [
                'success' => false,
                'data'    => sprintf(__('Impossible de déplacer le fichier "%s" dans le dossier d\'import', 'tify'), basename($file['name']))
            ];
        else :
            $response = [
                'success' => false,
                'data'    => ['filename' => $this->UploadDir . "/" . $filename]
            ];
        endif;

        wp_send_json($response);
    }

    /**
     * TRAITEMENT
     */
    /**
     * Traitement des arguments de requête
     */
    public function parse_query_args()
    {
        // Arguments par défaut
        parent::parse_query_args();

        if (isset($_REQUEST['filename'])) :
            $this->QueryArgs['filename'] = $_REQUEST['filename'];
        elseif ($this->Filename) :
            $this->QueryArgs['filename'] = is_array($this->Filename) ? current($this->Filename) : (string)$this->Filename;
        else :
            $this->QueryArgs['filename'] = '';
        endif;

        return $this->QueryArgs;
    }
    
    /**
     * Récupération de la réponse
     */
    protected function getResponse()
    {
        $params = $this->parse_query_args();
        
        if (empty($params['filename'])) :
            return;
        endif;

        $items = [];

        return $items;
    }
            
    /**
     * AFFICHAGE
     */        
    /**
     * Vues
     */
    public function views()
    {
        // Import de fichier personnel
        if( $this->Uploadable ) :
?>
<form class="tiFyTemplatesFileImport-Form tiFyTemplatesFileImport-Form--upload" method="post" action="" enctype="multipart/form-data" data-id="<?php echo $this->template()->getID() .'_'. self::classShortName();?>">              
    <strong><?php _e( 'Import de fichier personnel :', 'tify' );?></strong><br>
    
    <input class="tiFyTemplatesFileImportUploadForm-FileInput" type="file" autocomplete="off" />
    <span class="spinner tiFyTemplatesFileImportUploadForm-Spinner"></span>
</form> 
<?php
        endif;
        
        // Liste de fichiers à traiter
        if( is_array( $this->Filename ) ) :
?>
<form method="post" action="">
    <strong><?php _e( 'Choix du fichier à traiter :', 'tify' );?></strong><br>
    
    <select name="filename">
    <?php foreach( $this->Filename as $filename ) :?>
        <option value="<?php echo $filename;?>" <?php selected( $filename, $this->QueryArgs['filename'] );?>><?php echo $filename;?></option>        
    <?php endforeach;?>
    </select>
    <button type="submit" class="button-secondary"><?php _e('Traiter', 'tify');?></button>
</form>
<?php   endif;
        
        // Indication de fichier en traitement
        if ($this->QueryArgs['filename']) :
?>
<div class="tiFyTemplatesFileImport-handleFilename">
    <strong class="tiFyTemplatesFileImport-handleFilenameLabel"><?php _e( 'Fichier en cours de traitement :', 'tify' );?></strong>
    <div class="tiFyTemplatesFileImport-handleFilenameValue"><?php echo $this->QueryArgs['filename'];?></div>
</div>
<?php         
        endif;

        parent::views();
    }

    /**
     * Champs cachés
     */
    public function hidden_fields()
    {        
        parent::hidden_fields();
        
?><input type="hidden" id="ajaxActionFileImport" value="<?php echo $this->template()->getID() .'_'. self::classShortName() .'_fileimport_upload';?>" /><?php
    }
}
