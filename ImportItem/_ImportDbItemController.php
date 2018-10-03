<?php

namespace tiFy\Plugins\Transaction\ImportItem;

class ImportDbItemController extends AbstractImportItemController
{            
    /**
     * Base de données
     * @var \tiFy\Core\Db\Factory
     */
    protected $Db           = null;
    
    /**
     * Type de données prises en charge
     */
    protected $Types        = [
        'data',
        'meta'
    ];

    /**
     * Traitement des attributs d'import
     */
    public function parseAttrs($attrs = [])
    {
        $id = !empty($attrs['db']) ? $attrs['db'] : $this->setDb();
        if (!$id) :
            return $this->Notices->addError(__('Aucune base de donnée d\'import n\'a été définie', 'tify'), 'tiFyInheritsImport_AnyDb');
        elseif (!$this->Db = Db::get($id)) :
            return $this->Notices->addError(__('La base de donnée de données fournie semble invalide', 'tify'), 'tiFyInheritsImport_InvalidDb');
        endif;

        return $attrs;
    }
    
    /**
     * Définition de la base de données
     */
    public function setDb()
    {
        return null;
    }    
    
    /**
     * Insertion des données principales
     */
    public function insert_datas($dbarr, $insert_id)
    {
        $insert_id = $this->Db->handle()->record($dbarr);
        if(!$insert_id) :
            $this->Notices->addError(__('Impossible d\'enregister l\'élément', 'tify'), 'tFyDbImportDatasRecordFailed');
            $this->setSuccess(false);
        else :
            $this->Notices->addSuccess(__('L\'élement a été importé avec succès', 'tify'), 'tFyLibImportInsertDatasSuccess');
            $this->setInsertId($insert_id);
            $this->setSuccess(true);
        endif;

        return $insert_id;
    }
    
    /**
     * Insertion d'une métadonnée
     */
    public function insert_meta($meta_key, $meta_value, $insert_id)
    {
        return $this->Db->meta()->update($insert_id, $meta_key, $meta_value);
    }
}