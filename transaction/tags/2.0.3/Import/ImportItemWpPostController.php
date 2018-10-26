<?php

namespace tiFy\Plugins\Transaction\Import;

use Illuminate\Support\Arr;
use tiFy\Plugins\Transaction\Contracts\ImportItemWpPostInterface;

class ImportItemWpPostController extends ImportItemController implements ImportItemWpPostInterface
{
    /**
     * Cartographie des clés de données de sortie autorisées à être traitée.
     * @var array
     */
    protected $constraint = [
        'data' => [
            'ID',
            'post_author',
            'post_date',
            'post_date_gmt',
            'post_content',
            'post_content_filtered',
            'post_title',
            'post_excerpt',
            'post_status',
            'post_type',
            'comment_status',
            'ping_status',
            'post_password',
            'post_name',
            'to_ping',
            'pinged',
            'post_modified',
            'post_modified_gmt',
            'post_parent',
            'menu_order',
            'post_mime_type',
            'guid',
            'post_category',
            'tax_input',
            'meta_input'
        ]
    ];

    /**
     * Types de données pris en charge.
     * @var array {
     *  @var string $data Données principales.
     *  @var string $met Métadonnées.
     *  @var string $tax Taxonomies.
     * }
     */
    protected $types = [
        'data',
        'meta',
        'tax'
    ];

    /**
     * {@inheritdoc}
     */
    public function getOutputTax($taxonomy = null, $terms = [])
    {
        if (is_null($taxonomy)) :
            return Arr::get($this->output, 'tax', $terms);
        else :
            return Arr::get($this->output, "tax.{$taxonomy}", $terms);
        endif;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuccessMessage($post_id = null)
    {
        return sprintf(
            __('Le contenu "%s" a été importé avec succès', 'tify'),
            get_the_title($post_id)
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function getTypes()
    {
        return array_intersect($this->types, ['data', 'meta', 'tax']);
    }

    /**
     * {@inheritdoc}
     */
    public function insertData($postarr = [], $post_id = null)
    {
        if (!empty($postarr['ID'])) :
            $res = wp_update_post($postarr, true);
        else :
            $res = wp_insert_post($postarr, true);
        endif;

        if (is_wp_error($res)) :
            $this->notices()->add(
                'error',
                $res->get_error_message(),
                $res->get_error_data()
            );

            $this->setSuccess(false);
            $post_id = 0;
        else :
            $post_id = (int)$res;

            $this->notices()->add(
                'success',
                $this->getSuccessMessage($post_id),
                [
                    'post_id' => $post_id
                ]
            );

            $this->setSuccess(true);
        endif;

        $this->setPrimaryId($post_id);
    }

    /**
     * {@inheritdoc}
     */
    public function insertMeta($meta_key, $meta_value, $post_id = null)
    {
        return update_post_meta($post_id, $meta_key, $meta_value);
    }

    /**
     * {@inheritdoc}
     */
    public function insertTax($taxonomy, $terms, $post_id = null)
    {
        return wp_set_post_terms($post_id, $terms, $taxonomy);
    }

    /**
     * {@inheritdoc}
     */
    public function insertTaxAfter($taxonomies = [], $primary_id = null)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function insertTaxBefore($taxonomies = [], $primary_id = null)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function mapTax()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function outputSetTax($taxonomy, $raw_value = null)
    {
        return $raw_value;
    }

    /**
     * {@inheritdoc}
     */
    public function outputCheckTax($taxonomy, $terms = null, $post_id = null)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function outputFilterTax($taxonomy, $terms = null, $post_id = null)
    {
        return $terms;
    }
}