<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress;

use tiFy\Plugins\Transaction\{
    Contracts\ImportFactory as BaseImportFactoryContract,
    ImportFactory as BaseImportFactory,
    Wordpress\Contracts\ImportFactoryWpTerm as ImportFactoryWpTermContract};
use WP_Error;
use WP_Term;
use WP_Term_Query;

class ImportFactoryWpTerm extends BaseImportFactory implements ImportFactoryWpTermContract
{
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
     * Instance du post Wordpress associé.
     * @var WP_Term|null
     */
    protected $term;

    /**
     * @inheritDoc
     */
    public function execute(): BaseImportFactoryContract
    {
        $this
            ->fetchTaxonomy()
            ->fetchTermId()
            ->save();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchTermId(): ImportFactoryWpTermContract
    {
        if ($exists = (new WP_Term_Query())->query([
            'fields'     => 'ids',
            'hide_empty' => false,
            'include'    => $this->input('term_id', 0),
            'number'     => 1,
            'taxonomy'   => $this->getTaxonomy(),
        ])) {
            $term_id = (int)current($exists);
            $this->output(['term_id' => $term_id]);
            $this->setPrimary($term_id);
        } else {
            $this->output(['term_id' => 0]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchTaxonomy(): ImportFactoryWpTermContract
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
    public function setTaxonomy(string $taxonomy): ImportFactoryWpTermContract
    {
        $this->taxonomy = $taxonomy;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTerm(): ?WP_Term
    {
        return $this->term;
    }

    /**
     * @inheritDoc
     */
    public function save(): BaseImportFactoryContract
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
                ->setPrimary(0);
        } else {
            $term = get_term((int)$res['term_id'], $this->getTaxonomy());

            if ($term instanceof WP_Error) {
                $this->messages()->error($res->get_error_message(), (array)$res->get_error_data());

                $this
                    ->setSuccess(false)
                    ->setPrimary(0);
            } else {
                $this
                    ->setSuccess(true)
                    ->setPrimary($term->term_id)
                    ->messages()->success(
                        sprintf(
                            $update
                                ? __('%s : "%s" - id : "%d" >> mis(e) à avec succès.', 'tify')
                                : __('%s : "%s" - id : "%d" >> créé(e) avec succès.', 'tify'),
                            $this->getManager()->labels()->getSingular(),
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
    public function saveMetas(): ImportFactoryWpTermContract
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

    /**
     * {@inheritDoc}
     *
     * @return ImportFactoryWpTermContract
     */
    public function setPrimary($primary): BaseImportFactoryContract
    {
        parent::setPrimary($primary);

        $this->term = ($term = get_term((int)$primary, $this->getTaxonomy())) instanceof WP_Term ? $term : null;

        return $this;
    }
}