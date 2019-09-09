<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress;

use tiFy\Plugins\Transaction\{
    Contracts\ImportRecord as BaseImportRecordContract,
    ImportRecord as BaseImportRecord,
    Wordpress\Contracts\ImportWpPost as ImportWpPostContract
};
use WP_Error;
use WP_Post;
use WP_Query;

class ImportWpPost extends BaseImportRecord implements ImportWpPostContract
{
    /**
     * Cartographie des clés de données de post.
     * @var array
     */
    protected $keys = [
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
        'meta_input',
    ];

    /**
     * Instance du post Wordpress associé.
     * @var WP_Post|false|null
     */
    protected $exists;

    /**
     * @inheritDoc
     */
    public function execute(): BaseImportRecordContract
    {
        $this->prepare()->save()->saveInfos();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function exists(): ?WP_Post
    {
        return parent::exists();
    }

    /**
     * @inheritDoc
     */
    public function fetchExists(): BaseImportRecordContract
    {
        if (is_null($this->exists)) {
            if ($exists = (new WP_Query())->query([
                'p'              => $this->input('ID', 0),
                'post_type'      => 'any',
                'posts_per_page' => 1,
            ])) {
                $this->setExists(current($exists));
                $this->output(['ID' => $this->exists()->ID]);
            } else {
                $this->setExists(false);
                $this->output(['ID' => 0]);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPost(): ?WP_Post
    {
        return $this->exists ?: null;
    }

    /**
     * {@inheritDoc}
     *
     * @return ImportWpPostContract
     */
    public function save(): BaseImportRecordContract
    {
        $postarr = array_intersect_key($this->output->all(), array_flip($this->keys));

        if (!empty($postarr['ID'])) {
            $res = wp_update_post($postarr, true);
            $update = true;
        } else {
            $res = wp_insert_post($postarr, true);
            $update = false;
        }

        if ($res instanceof WP_Error) {
            $this->messages()->error($res->get_error_message(), (array)$res->get_error_data());

            $this
                ->setSuccess(false)
                ->setExists(false);
        } else {
            if (!$post = get_post((int)$res)) {
                $this
                    ->setSuccess(false)
                    ->setExists(false)
                    ->messages()->error(__('Impossible de récupérer le post importé', 'tify'));
            } else {
                $this
                    ->setSuccess(true)
                    ->setExists($post)
                    ->messages()->success(
                        sprintf(
                            $update
                                ? __('%s : "%s" - id : "%d" >> mis(e) à jour avec succès.', 'tify')
                                : __('%s : "%s" - id : "%d" >> créé(e) avec succès.', 'tify'),
                            $this->records()->labels()->singular(),
                            html_entity_decode($post->post_title),
                            $post->ID
                        ),
                        ['post' => $post->to_array()]
                    );

                $this->saveMetas()->saveTerms();
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function saveMetas(): ImportWpPostContract
    {
        if ($post = $this->getPost()) {
            foreach ($this->output('_meta', []) as $meta_key => $meta_value) {
                if (!update_post_meta($post->ID, $meta_key, $meta_value)) {
                    $this->messages()->debug(
                        sprintf(__('La métadonnée "%s" n\'a pas été enregistrée.', 'tify'), $meta_key),
                        ['meta_key' => $meta_key, 'meta_value' => $meta_value, 'post' => $post->to_array()]
                    );
                };
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function saveTerms(): ImportWpPostContract
    {
        if ($post = $this->getPost()) {
            foreach ($this->output('_term', []) as $taxonomy => $terms) {
                $res = wp_set_post_terms($post->ID, $terms, $taxonomy);

                if ($res instanceof WP_Error) {
                    $this->messages()->debug(
                        sprintf(__('Les termes de taxonomie "%s" n\'ont été enregistrés.', 'tify'), $taxonomy),
                        ['terms' => $terms, 'taxonomy' => $taxonomy, 'post' => $post->to_array()]
                    );
                }
            }
        }

        return $this;
    }
}