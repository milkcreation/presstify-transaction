<?php
namespace tiFy\Core\Templates\Admin\Model\Export;
 
class Export extends \tiFy\Core\Templates\Admin\Model\Table
{
    /**
     * PARAMETRAGE
     */
    public function set_page_title()
    {
        return $this->label( 'export_items' );
    }    

    /**
     * CONSTRUCTEUR
     */
    public function __construct()
    {
        add_filter( 'upload_mimes', array( $this, 'upload_mimes' ), 10, 2 );
        add_action( 'tify_upload_register', array( $this, 'tify_upload_register' ) );    
    }

    /**
     * DECLENCHEURS
     */
    /**
     * Initialisation de l'interface d'administration privée
     * {@inheritDoc}
     * @see \tiFy\Core\Templates\Admin\Model\Table::_admin_init()
     */
    public function _admin_init()
    {
        parent::_admin_init();
        
        add_action( 'wp_ajax_'. $this->template()->getID() .'_'. self::classShortName() .'_export_items', array( $this, 'wp_ajax_export_items' )  );
    }

    /**
     * Déclaration des scripts
     */
    public function admin_enqueue_scripts()
    {        
        parent::admin_enqueue_scripts();
        
        tify_control_enqueue( 'progress' );
        wp_enqueue_script( 'tiFyCoreTemplatesAdminModelExport', self::tFyAppUrl( get_class() ) .'/Export.js', array( 'jquery' ), 161217, true );
    }

    /** == Autorisation de téléchargement du type de fichier == **/
    final public function upload_mimes( $mime_types, $user )
    {
        $mime_types['csv'] =  'text/csv';
        
        return $mime_types;
    }
    
    /** == Autorisation de téléchargement de fichier == **/
    final public function tify_upload_register()
    {
        if( ! $transient = get_transient( 'tify_forms_record_export_allowed_file_upload' ) )
            return;
        if( ! isset( $_REQUEST['_wpnonce'] ) )
            return;
        $filename = basename( tify_upload_get( 'url' ) );
        
        if( ! wp_verify_nonce( $_REQUEST['_wpnonce'], "tify_forms_record_export-". $filename ) )
            return;

        if( $filename === $transient )
            tify_upload_register( $this->main->master->dirs->uri( 'export' ) .'/'. $filename );
    }

    /**
     * 
     */
    public function wp_ajax_export_items()
    {           
        // Définition des paramètres d'export
        if( empty( $_REQUEST['_transient'] ) ) :            
            $transient = 'tiFyTemplatesExport_'. uniqid();
            
            // Pagination
            $_REQUEST['paged'] = 1;
        
            // Données
            $basedir = WP_CONTENT_DIR .'/uploads';        
            $url = WP_CONTENT_URL .'/uploads';
            $file = 'export-'. uniqid() .'.csv';
            
            // Fichier d'export    
            $fp = fopen( $basedir. '/'. $file, 'w' );            
        else :
            $transient = $_REQUEST['_transient'];
            $datas = get_transient( $transient );
            
            // Pagination
            $_REQUEST['paged'] = ++$datas['paged'];
            
            // Données
            extract( $datas );
            
            // Fichier d'export    
            $fp = fopen( $basedir. '/'. $file, 'a' );    
        endif;
        
        $query_args = wp_parse_args( array( '_transient' => $transient ), $_REQUEST );
        
        // Récupération des éléments
        $this->initParams();
        $this->prepare_items();
        
        // Création des lignes de données
        $rows = array(); $i = 0;
        foreach( $this->items as $item ) :
            foreach( $this->get_columns() as $column_name => $column_label ) :
                if( $column_name === 'cb')
                    continue;
                if ( method_exists( $this, '_column_' . $column_name ) ) :
                    $rows[$i][] = call_user_func( array( $this, '_column_' . $column_name ), $item );
                elseif ( method_exists( $this, 'column_' . $column_name ) ) :
                    $rows[$i][] = call_user_func( array( $this, 'column_' . $column_name ), $item );
                else :
                    $rows[$i][] = $this->column_default( $item, $column_name );
                endif;        
            endforeach;
            $i++;
        endforeach;
        
        // Ecriture du fichiers csv
        foreach( $rows as $row ) :    
            fputcsv( $fp, $row, ';', '"' );
        endforeach;                
        fclose($fp);
        
        // Sauvegarde des données d'export
        $datas = array(
            'basedir'           => $basedir,
            'url'               => $url,
            'file'              => $file,
            'upload_url'        => tify_upload_url( $url .'/'. $file, array( 'authorize' => $transient ) ),
            'title'             => sprintf( __( 'Télécharger le fichier %s', 'tify' ), $file ),
            'paged'             => (int) $_REQUEST['paged'],
            'total_items'       => (int) $this->get_pagination_arg( 'total_items' ),
            'total_pages'       => (int) $this->get_pagination_arg( 'total_pages' ),
            'per_page'          => (int) $this->get_pagination_arg( 'per_page' ),
            'query_args'        => $query_args
        );
        set_transient( $transient, $datas, HOUR_IN_SECONDS );
        
        wp_send_json_success( $datas );
    }
    
    /**
     * AFFICHAGE
     */
    /**
     * Champs cachés
     */
    public function hidden_fields()
    {                
        $ajaxData = array(
            'action' => $this->template()->getID() .'_'. self::classShortName() .'_export_items'            
        );
?>
<input type="hidden" id="ajaxData" value="<?php echo urlencode( json_encode( $ajaxData ) );?>" />
<?php
    }

    /**
     * 
     * {@inheritDoc}
     * @see \tiFy\Core\Templates\Admin\Model\Table::render()
     */
    public function render()
    {
?>
<div class="wrap">
    <h2>
        <?php echo $this->PageTitle;?>
    </h2>
    <div style="margin-right:300px;">
        <div style="float:left; width: 100%;">
            <?php $this->views(); ?>

            <form method="get" action="">
                <?php parse_str( parse_url( $this->BaseUri, PHP_URL_QUERY ), $query_vars ); ?>
                <?php foreach( (array) $query_vars as $name => $value ) : ?>
                    <input type="hidden" name="<?php echo $name;?>" value="<?php echo $value;?>" />
                <?php endforeach;?>
                <?php $this->hidden_fields();?>
            
                <?php $this->search_box( $this->label( 'search_items' ), $this->template()->getID() );?>
                <?php $this->display();?>
                <?php $this->inline_preview();?>
            </form>       
        </div>
        <div id="side-sortables" style="margin-top:9px; margin-right:-300px; width: 280px; float:right;">
            <div id="submitdiv" class="tify_submitdiv">
                <h3 class="hndle">
                    <span><?php _e( 'Exporter', 'tify' );?></span>
                </h3>
                <div class="inside">
                    <div class="minor_actions">
                        <div id="tiFyTemplatesExport-Download" style="margin:0;padding:10px;">
                            <span id="tiFyTemplatesExport-DownloadFile"></span>
                            <?php tify_control_progress( array( 'class' => 'tiFyTemplatesExport-ProgressBar' ) );?>
                        </div>
                    </div>    
                    <div class="major_actions">
                        <button type="submit" id="tiFyTemplatesExport-Submit" class="button-primary"><?php _e( 'Lancer l\'export', 'tify' );?></button>
                    </div>    
                </div>
            </div>                    
        </div>
    </div>            
</div>
<?php
    }
}