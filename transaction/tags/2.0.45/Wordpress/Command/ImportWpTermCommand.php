<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Command;

use Exception;
use Illuminate\Database\Eloquent\{Builder, Collection as BaseCollection, Model as BaseModel};
use Symfony\Component\Console\{Input\InputInterface, Output\OutputInterface};
use tiFy\Wordpress\Database\Model\TermTaxonomy as TermTaxonomyModel;
use WP_Error;

class ImportWpTermCommand extends ImportWpBaseCommand
{
    /**
     * Indicateur de traitement hierarchique
     * @var bool
     */
    protected $hierachical = true;

    /**
     * Identifiant de qualification de la taxonomie d'origine (entrée).
     * @var string|null
     */
    protected $inTaxonomy;

    /**
     * Identifiant de qualification de la taxonomie d'enregistrement (sortie).
     * @var string|null
     */
    protected $outTaxonomy;

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handleBefore();

        parent::execute($input, $output);

        $this->buildQuery()->chunkById($this->chunk, function (BaseCollection $collect) use ($output) {
            $this->handleCollection($collect, $output);
        });

        $this->handleAfter();
    }

    /**
     * @inheritDoc
     */
    public function buildQuery(): Builder
    {
        $collect = $this->getInModel()->offset($this->getOffset());

        if ($ids = $this->contraintIds) {
            $collect->whereIn('term_id', $ids);
        } else {
            $collect->where($this->queryArgs);
        }

        return $collect;
    }

    /**
     * Traitement des résultats de requête.
     *
     * @param BaseCollection $collect
     * @param OutputInterface $output
     * @param int|null $parent
     *
     * @return void
     *
     * @throws Exception
     */
    protected function handleCollection(BaseCollection $collect, OutputInterface $output, ?int $parent = null)
    {
        foreach ($collect as $model) {
            $this->counter++;

            $this->handleItemBefore($model);

            try {
                $id = $this->insertOrUpdate($model, is_null($parent)
                    ? $this->getRelatedTermId($model->parent) : $parent
                );

                if ($this->isHierarchical()) {
                    $args = array_merge($this->queryArgs, [
                        'taxonomy' => $this->getInTaxonomy(),
                        'parent'   => $model->term_id,
                    ]);

                    $this->getInModel()->where($args)
                        ->chunkById($this->chunk, function (BaseCollection $collect) use ($output, $id) {
                            $this->handleCollection($collect, $output, $id);
                        });
                }

                $this->handleItemAfter($id, $model);
            } catch (Exception $e) {
                $this->message()->error($e->getMessage());
            }

            $this->outputMessages($output);
        }
    }

    /**
     * Création ou mise à jour.
     *
     * @param TermTaxonomyModel $model
     * @param int $parent
     *
     * @return int
     *
     * @throws Exception
     */
    protected function insertOrUpdate(TermTaxonomyModel $model, int $parent): int
    {
        $this->data()->clear();

        $this->parseTermdata($model, ['parent' => $parent]);

        if ($id = $this->getRelatedTermId($model->term_id)) {
            $taxonomy = $this->data()->pull('taxonomy', '');

            $res = wp_update_term($id, $taxonomy, $this->data()->all());

            if (!$res instanceof WP_Error) {
                $this->importer()->addWpTerm(
                    $res['term_id'], $model->term_id, $this->withCache ? $model->toArray() : []
                );

                $this->message()->success(sprintf(
                    __('%d -- SUCCES: Mise à jour du terme de taxonomie [#%d - %s] depuis [#%d].', 'tify'),
                    $this->counter, $res['term_id'], $model->name, $model->term_id
                ));

                return $res['term_id'];
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Mise à jour le terme de taxonomie [#%d] depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $id, $model->term_id, $model->name, $res->get_error_message(), $model->toJson()
                ));
            }
        } else {
            $taxonomy = $this->data()->pull('taxonomy', '');

            $res = wp_insert_term($model->name, $taxonomy, $this->data()->all());

            if (!$res instanceof WP_Error && !empty($res['term_id'])) {
                $this->importer()->addWpTerm(
                    $res['term_id'], $model->term_id, $this->withCache ? $model->toArray() : []
                );

                $this->message()->success(sprintf(
                    __('%d -- SUCCES: Création du terme de taxonomie [#%d - %s] depuis [#%d].'),
                    $this->counter, $res['term_id'], $model->name, $model->term_id
                ));

                return $res['term_id'];
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Création du terme de taxonomie depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $model->term_id, $model->name, $res->get_error_message(), $model->toJson()
                ));
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @return TermTaxonomyModel|Builder
     */
    public function getInModel(): ?BaseModel
    {
        $classname = $this->inModelClassname;

        return ($instance = new $classname()) instanceof TermTaxonomyModel ? $instance : null;
    }

    /**
     * Récupération du nom de qualification de la taxonomie d'origine (entrée).
     *
     * @return string
     */
    public function getInTaxonomy(): ?string
    {
        return $this->inTaxonomy;
    }

    /**
     * {@inheritDoc}
     *
     * @return TermTaxonomyModel|Builder
     */
    public function getOutModel(): ?BaseModel
    {
        $classname = $this->outModelClassname;

        return ($instance = new $classname()) instanceof TermTaxonomyModel ? $instance : null;
    }

    /**
     * Récupération du nom de qualification de la taxonomie d'enregistrement (sortie).
     *
     * @return string
     */
    public function getOutTaxonomy(): ?string
    {
        return $this->outTaxonomy;
    }

    /**
     * Vérification d'activation du traitement hierarchique.
     *
     * @return bool
     */
    public function isHierarchical(): bool
    {
        return $this->hierachical;
    }

    /**
     * Traitement des données du terme de taxonomie à enregistrer selon le modèle d'entrée et données personnalisées.
     *
     * @param TermTaxonomyModel $model
     * @param array $attrs Liste des attributs personnalisés.
     *
     * @return static
     */
    public function parseTermdata(TermTaxonomyModel $model, array $attrs = []): self
    {
        $this->data(array_merge([
            'description' => $model->description,
            'name'        => $model->name,
            'parent'      => $model->parent,
            'slug'        => $model->slug,
            'taxonomy'    => $this->getOutTaxonomy() ?: $model->taxonomy,
        ], $attrs));

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return ImportWpTermCommand
     */
    public function parseQueryArgs(): ImportWpBaseCommand
    {
        $args = [];

        if ($this->isHierarchical()) {
            $args['parent'] = 0;
        }

        $this->queryArgs = array_merge($args, $this->queryArgs, [
            'taxonomy' => $this->getInTaxonomy(),
        ]);

        return $this;
    }

    /**
     * Définition de l'activation du traitement hiérarchique.
     *
     * @param bool $hierarchical
     *
     * @return static
     */
    public function setHierarchical(bool $hierarchical = true): self
    {
        $this->hierachical = $hierarchical;

        return $this;
    }

    /**
     * Définition de l'identifiant de qualification de la taxonomie d'origine (entrée).
     *
     * @param string $taxonomy
     *
     * @return static
     */
    public function setInTaxonomy(string $taxonomy): self
    {
        $this->inTaxonomy = $taxonomy;

        return $this;
    }

    /**
     * Définition de l'identifiant de qualification de la taxonomie d'enregistrement (sortie).
     *
     * @param string $taxonomy
     *
     * @return static
     */
    public function setOutTaxonomy(string $taxonomy): self
    {
        $this->outTaxonomy = $taxonomy;

        return $this;
    }
}
