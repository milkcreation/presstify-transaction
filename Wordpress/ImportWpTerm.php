<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress;

use tiFy\Plugins\Transaction\{
    Contracts\ImportRecord as BaseImportRecordContract,
    ImportRecord as BaseImportRecord,
    Wordpress\Contracts\ImportWpTerm as ImportWpTermContract
};
use WP_Error;
use WP_Term;
use WP_Term_Query;

class ImportWpTerm extends BaseImportRecord implements ImportWpTermContract
{
    /**
     * Instance du post Wordpress associé.
     * @var WP_Term|null
     */
    protected $exists;

    /**
     * Cartographie des clés de données de terme.
     * @var array
     */
    protected $keys = [
        'term_id',
        'name',
        'slug',
        'term_group',
        'term_taxonomy_id',
        'taxonomy',
        'description',
        'parent',
        'count',
    ];

    /**
     * Nom de qualification de la taxonomie associée.
     * @var string
     */
    protected $taxonomy = '';

    /**
     * @inheritDoc
     */
    public function execute(): BaseImportRecordContract
    {
        $this->prepare()->save();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function exists(): ?WP_Term
    {
        return parent::exists();
    }

    /**
     * @inheritDoc
     */
    public function fetchExists(): BaseImportRecordContract
    {
        if (is_null($this->exists)) {
            if ($exists = (new WP_Term_Query())->query([
                'hide_empty' => false,
                'include'    => $this->input('term_id', 0),
                'number'     => 1,
                'taxonomy'   => $this->getTaxonomy(),
            ])) {
                $this->setExists(current($exists));
                $this->output(['term_id' => $this->exists()->term_id]);
            } else {
                $this->setExists(false);
                $this->output(['term_id' => 0]);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchTaxonomy(): ImportWpTermContract
    {
        if ($taxonomy = $this->input('taxonomy')) {
            $this->taxonomy = $taxonomy;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTaxonomy(): string
    {
        return $this->taxonomy;
    }

    /**
     * @inheritDoc
     */
    public function setTaxonomy(string $taxonomy): ImportWpTermContract
    {
        $this->taxonomy = $taxonomy;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTerm(): ?WP_Term
    {
        return $this->exists ?: null;
    }

    /**
     * @inheritDoc
     */
    public function prepare(): BaseImportRecordContract
    {
        if (!$this->prepared) {
            $this
                ->fetchTaxonomy()
                ->fetchExists();

            $this->prepared = true;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function save(): BaseImportRecordContract
    {
        $datas = array_intersect_key($this->output->all(), array_flip($this->keys));

         if (!empty($datas['term_id'])) {
             $res = wp_update_term($datas['term_id'], $this->getTaxonomy(), $datas);
             $update = true;
         } else {
             $res = wp_insert_term($datas['name'], $this->getTaxonomy(), $datas);
             $update = false;
         }

        if ($res instanceof WP_Error) {
            $this->messages()->error($res->get_error_message(), (array)$res->get_error_data());

            $this
                ->setSuccess(false)
                ->setExists(false);
        } else {
            $term = get_term((int)$res['term_id'], $this->getTaxonomy());

            if ($term instanceof WP_Error) {
                $this->messages()->error($res->get_error_message(), (array)$res->get_error_data());

                $this
                    ->setSuccess(false)
                    ->setExists(false);
            } else {
                $this
                    ->setSuccess(true)
                    ->setExists($term)
                    ->messages()->success(
                        sprintf(
                            $update
                                ? __('%s : "%s" - id : "%d" >> mis(e) à avec succès.', 'tify')
                                : __('%s : "%s" - id : "%d" >> créé(e) avec succès.', 'tify'),
                            $this->records()->labels()->singular(),
                            html_entity_decode($term->name),
                            $term->term_id
                        ), ['term' => get_object_vars($term)]
                    );

                $this->saveMetas();
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function saveMetas(): ImportWpTermContract
    {
        if ($term = $this->getTerm()) {
            foreach ($this->output('_meta', []) as $meta_key => $meta_value) {
                $res = update_term_meta($term->term_id, $meta_key, $meta_value);
                if ($res instanceof WP_Error) {
                    $this->messages()->debug(
                        sprintf(__('La métadonnée "%s" n\'a pas été enregistrée.', 'tify'), $meta_key),
                        compact('meta_key', 'meta_value')
                    );
                }
            }
        }

        return $this;
    }
}