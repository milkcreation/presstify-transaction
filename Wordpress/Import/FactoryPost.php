<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Import;

use tiFy\Plugins\Transaction\{
    Import\Factory as ImportFactory,
    Wordpress\Contracts\ImportFactoryPost as ImportFactoryPostContract
};

class FactoryPost extends ImportFactory implements ImportFactoryPostContract
{
    /**
     * Cartographie des clés de données de sortie autorisées à être traitée.
     * @var array
     */
    protected $constraints = [
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
    ];

    /**
     * @inheritDoc
     */
    public function insertData(array $postarr = [], $post_id = null)
    {
        $res = !empty($postarr['ID']) ? wp_update_post($postarr, true) : wp_insert_post($postarr, true);

        if (is_wp_error($res)) {
            $post_id = 0;

            $this->messages()->error($res->get_error_message(), (array)$res->get_error_data());

            $this->setSuccess(false);
        } else {
            $post_id = (int)$res;

            $this->messages()->success(__('Le contenu a été importé avec succès', 'theme'), [
                'post_id' => $post_id
            ]);

            $this->setSuccess(true);
        }

        $this->setPrimary($post_id);
    }

    /**
     * @inheritDoc
     */
    public function insertMeta($meta_key, $meta_value, $post_id = null)
    {
        return update_post_meta($post_id, $meta_key, $meta_value);
    }

    /**
     * @inheritDoc
     */
    public function insertTax($taxonomy, $terms, $post_id = null)
    {
        return wp_set_post_terms($post_id, $terms, $taxonomy);
    }
}