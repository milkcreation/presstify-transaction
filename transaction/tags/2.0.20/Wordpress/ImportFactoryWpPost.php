<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress;

use tiFy\Plugins\Transaction\{
    Contracts\ImportFactory as BaseImportFactoryContract,
    ImportFactory as BaseImportFactory,
    Wordpress\Contracts\ImportFactoryWpPost as ImportFactoryWpPostContract};
use WP_Error;
use WP_Post;
use WP_Query;

class ImportFactoryWpPost extends BaseImportFactory implements ImportFactoryWpPostContract
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
     * @var WP_Post|null
     */
    protected $post;

    /**
     * @inheritDoc
     */
    public function fetchID(): ImportFactoryWpPostContract
    {
        if ($exists = (new WP_Query())->query([
            'fields'         => 'ids',
            'p'              => $this->input('ID', 0),
            'post_type'      => 'any',
            'posts_per_page' => 1,
        ])) {
            $post_id = (int)current($exists);
            $this->output(['ID' => $post_id]);
            $this->setPrimary($post_id);
        } else {
            $this->output(['ID' => 0]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function execute(): BaseImportFactoryContract
    {
        $this
            ->fetchID()
            ->save();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPost(): ?WP_Post
    {
        return $this->post;
    }

    /**
     * {@inheritDoc}
     *
     * @return ImportFactoryWpPostContract
     */
    public function save(): BaseImportFactoryContract
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
                ->setPrimary(0);
        } else {
            if (!$post = get_post((int)$res)) {
                $this
                    ->setSuccess(false)
                    ->setPrimary(0)
                    ->messages()->error(__('Impossible de récupérer le post importé', 'tify'));
            } else {
                $this
                    ->setSuccess(true)
                    ->setPrimary($post->ID)
                    ->messages()->success(
                        sprintf(
                            $update
                                ? __('Le post "%s" a été mis à jour avec succès.', 'tify')
                                : __('Le post "%s" a été crée avec succès.', 'tify'),
                            html_entity_decode($post->post_title)
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
    public function saveMetas(): ImportFactoryWpPostContract
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
    public function saveTerms(): ImportFactoryWpPostContract
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

    /**
     * {@inheritDoc}
     *
     * @return ImportFactoryWpPostContract
     */
    public function setPrimary($primary): BaseImportFactoryContract
    {
        parent::setPrimary($primary);

        $this->post = get_post($primary);

        return $this;
    }
}