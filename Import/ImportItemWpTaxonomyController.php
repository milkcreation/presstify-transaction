<?php

namespace tiFy\Plugins\Transaction\Import;

use tiFy\Plugins\Transaction\Contracts\ImportItemWpTaxonomyInterface;

class ImportItemWpTaxonomyController extends ImportItemController implements ImportItemWpTaxonomyInterface
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
    public function getTaxonomy()
    {
        return $this->taxonomy;
    }

    /**
     * {@inheritdoc}
     */
    public function insertData($datas, $term_id = null)
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
                $term->get_error_data()
            );

            $this->setSuccess(false);
            $term_id = 0;
        else :
            $term_id = (int)$term['term_id'];

            $this->notices()->add(
                'success',
                __('La catégorie a été importé avec succès', 'tify'),
                [
                    'term_id' => $term_id
                ]
            );

            $this->setSuccess(true);
        endif;

        $this->setPrimaryId($term_id);
    }

    /**
     * {@inheritdoc}
     */
    public function insert_meta($meta_key, $meta_value, $term_id)
    {
        return update_term_meta($term_id, $meta_key, $meta_value);
    }
}