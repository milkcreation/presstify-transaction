<?php

namespace tiFy\Plugins\Transaction\Wp\Import;

use tiFy\Plugins\Transaction\Import\ImportFactory;
use tiFy\Plugins\Transaction\Wp\Contracts\ImportFactoryTerm as ImportFactoryTermContract;
use WP_Term;

class ImportFactoryTerm extends ImportFactory implements ImportFactoryTermContract
{
    /**
     * Cartographie des clés de données de sortie autorisées à être traitée.
     * @var array
     */
    protected $constraint = [
        'data' => [
            'term_id',
            'name',
            'slug',
            'term_group',
            'term_taxonomy_id',
            'taxonomy',
            'description',
            'parent',
            'count'
        ]
    ];

    /**
     * Types de données pris en charge.
     * @var array {
     * @var string $data Données principales.
     * @var string $met Métadonnées.
     * }
     */
    protected $types = [
        'data',
        'meta'
    ];

    /**
     * Nom de qualification de la taxonomie à traiter.
     * @var string
     */
    protected $taxonomy = '';

    /**
     * {@inheritdoc}
     */
    final public function getTypes()
    {
        return array_intersect($this->types, ['data', 'meta']);
    }

    /**
     * {@inheritdoc}
     */
    public function parse($attrs = [])
    {
        parent::parse($attrs);

        $this->taxonomy = $this->get('taxonomy', $this->getTaxonomy());

        if (!$this->getTaxonomy()) :
            $this->notices()->add(
                'error',
                __('Taxonomie de traitement manquante', 'tify')
            );
            $this->setOnBreak();
        endif;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuccessMessage($term_id = null)
    {
        $term = get_term($term_id, $this->taxonomy);

        if($term instanceof WP_Term) :
            return sprintf(
                __('La catégorie "%s" a été importé avec succès.', 'tify'),
                $term->name
            );
        else :
            return '';
        endif;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaxonomy()
    {
        return $this->taxonomy;
    }

    /**
     * {@inheritdoc}
     */
    public function insertData($datas = [], $term_id = null)
    {
        if (empty($datas['term_id'])) :
            $res = wp_insert_term($datas['name'], $this->taxonomy, $datas);
        else :
            $res = wp_update_term($datas['term_id'], $this->taxonomy, $datas);
        endif;

        if (is_wp_error($res)) :
            $this->notices()->add(
                'error',
                $res->get_error_message(),
                $res->get_error_data()
            );

            $this->setSuccess(false);
            $term_id = 0;
        else :
            $term_id = (int)$res['term_id'];

            if ($message = $this->getSuccessMessage($term_id)) :
                $this->notices()->add(
                    'success',
                    $this->getSuccessMessage($term_id),
                    [
                        'term_id' => $term_id
                    ]
                );
                $this->setSuccess(true);
            else :
                $this->notices()->add(
                    'error',
                    __(
                        'La catégorie "%s" a été importé avec succès, mais il semble impossible de la récupérer.',
                        'tify'
                    )
                );

                $this->setSuccess(false);
                $term_id = 0;
            endif;
        endif;

        $this->setPrimaryId($term_id);
    }

    /**
     * {@inheritdoc}
     */
    public function insertMeta($meta_key, $meta_value, $term_id = null)
    {
        return update_term_meta($term_id, $meta_key, $meta_value);
    }
}