<?php
namespace tiFy\Core\Templates\Admin\Model\ExportUser;

class ExportUser extends \tiFy\Core\Templates\Admin\Model\Export\Export
{    
    /**
     * Roles des utilisateurs de la table
     */
    protected $Roles                 = array();

    /**
     * PARAMETRAGE
     */
    /** 
     * Définition de la cartographie des paramètres autorisés
     */
    public function set_params_map()
    {
        $params = parent::set_params_map();        
        array_push( $params, 'Roles' );
        
        return $params;
    }

    /**
     * Initialisation des rôles des utilisateurs de la table
     */
    public function initParamRoles()
    {        
        if( $editable_roles = array_reverse( get_editable_roles() ) )
            $editable_roles = array_keys( $editable_roles );
        
        $roles = array();
        if( $this->set_roles() ) :            
            foreach( (array) $this->set_roles() as $role ) :
                if( ! in_array( $role, $editable_roles ) ) 
                    continue;
                array_push(  $roles, $role );
            endforeach;
        else :
            $roles = $editable_roles;
        endif;
        
        $this->Roles = $roles;
    }

    /**
     * TRAITEMENT
     */
    /**
     * Récupération des éléments
     */
    public function prepare_items()
    {        
        // Récupération des items
        $query = new \WP_User_Query( $this->parse_query_args() );
        $this->items = $query->get_results();

        // Pagination
        $total_items     = $query->get_total();
        $per_page         = $this->get_items_per_page( $this->db()->Name, $this->PerPage );
        
        $this->set_pagination_args( 
            array(
                'total_items' => $total_items,
                'per_page'    => $this->get_items_per_page( $this->db()->Name, $this->PerPage ),
                'total_pages' => ceil( $total_items / $per_page )
            )
        );
    }

    /**
     * Traitement des arguments de requête
     */
    public function parse_query_args()
    {
        // Récupération des arguments
        $per_page     = $this->get_items_per_page( $this->db()->Name, $this->PerPage );
        $paged         = $this->get_pagenum();
                
        // Arguments par défaut
        $query_args = array(
            'number'            => $per_page,
            'paged'             => $paged,
            'count_total'       => true,
            'fields'            => 'all_with_meta',
            'orderby'           => 'user_registered',
            'order'             => 'DESC',
            'role__in'          => $this->Roles
        );
        
        // Traitement des arguments
        foreach( (array) $_REQUEST as $key => $value ) :
            if( method_exists( $this, 'parse_query_arg_' . $key ) ) :
                 call_user_func_array( array( $this, 'parse_query_arg_' . $key ), array( &$query_args, $value ) );
            elseif( $this->db()->isCol( $key ) ) :
                $query_args[$key] = $value;
            endif;
        endforeach;

        return wp_parse_args( $this->QueryArgs, $query_args );
    }

    /**
     * Traitement de l'argument de requête > terme de recherche
     */
    public function parse_query_arg_s( &$query_args, $value )
    {
        if( ! empty( $value ) )
            $query_args['search'] = '*'. wp_unslash( trim( $value ) ) .'*';
    }

    /**
     * Traitement de l'argument de requête > role
     */
    public function parse_query_arg_role( &$query_args, $value )
    {
        if( ! empty( $value ) ) :
            if( is_string( $value ) ) :
                $value = array_map( 'trim', explode( ',', $value ) );
            endif;
            $roles = array();
            foreach( $value as $v ) :
                if( ! in_array( $v, $this->Roles ) )
                    continue;
                array_push( $roles, $v );
            endforeach;
            if( $roles ) :
                $query_args['role__in'] = $roles;
            endif;
        endif;
    }
    
    /**
     * Compte le nombre d'éléments
     */
    public function count_items( $args = array() )
    {
        if( $query = new \WP_User_Query( $args ) ) :
            return $query->get_total();
        else :
            return 0;
        endif;
    }
}