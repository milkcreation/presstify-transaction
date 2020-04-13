<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Command;

use Exception;
use Illuminate\Database\Eloquent\{Builder, Collection as BaseCollection};
use Symfony\Component\Console\Output\OutputInterface;
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
     * Clé primaire.
     *
     * @var string|null
     */
    protected $primaryKey = 'term_id';

    /**
     * Identifiant de qualification de la taxonomie d'enregistrement (sortie).
     * @var string|null
     */
    protected $outTaxonomy;

    /**
     * @inheritDoc
     */
    public function countQuery(): Builder
    {
        if ($this->isHierarchical() && !$this->getContraintIds()) {
            $args = $this->getQueryArgs();
            unset($args['parent']);

            return $this->getBuilder()->where($args);
        } else {
            return parent::countQuery();
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param BaseCollection|TermTaxonomyModel[] $items
     * @param OutputInterface $output
     * @param int|null $parent
     */
    public function handleItems(BaseCollection $items, OutputInterface $output, ?int $parent = null): void
    {
        foreach ($items as $item) {
            $this->itemDatas()->clear();

            $this->counter++;

            $this->handleItemBefore($item);

            try {
                $res = $this->insertOrUpdate($item, is_null($parent)
                    ? $this->getRelatedTermId($item->parent) : $parent
                );

                if ($this->isHierarchical()) {
                    $args = array_merge($this->getQueryArgs(), [
                        'taxonomy' => $this->getInTaxonomy(),
                        'parent'   => $item->term_id,
                    ]);

                    $insert_id = $res['insert_id'] ?? 0;

                    $this->getBuilder()->where($args)
                        ->chunkById($this->getChunk(), function (BaseCollection $collect) use ($output, $insert_id) {
                            $this->handleItems($collect, $output, $insert_id);
                        });
                }
            } catch (Exception $e) {
                $this->message()->error($e->getMessage());
            }

            $this->itemDatas()->set($res ?? []);

            $this->handleItemAfter($item);

            $this->handleMessages($output);
        }
    }

    /**
     * Création ou mise à jour.
     *
     * @param TermTaxonomyModel $item
     * @param int $parent
     *
     * @return array {
     *  @type int $insert_id
     *  @type bool $update
     * }
     *
     * @throws Exception
     */
    public function insertOrUpdate(TermTaxonomyModel $item, int $parent): array
    {
        if ($id = $this->getRelatedTermId($item->term_id)) {
            if (!$this->isUpdatable()) {
                $this->message()->info(sprintf(
                    __('%s > INFO: Le terme de taxonomie a déjà été importé [#%d - %s] depuis [#%d].', 'tify'),
                    $this->getCounter(), $id, $item->name, $item->term_id
                ));

                return ['insert_id' => $id, 'update' => true];
            }

            $this->parseTermdata($item, ['parent' => $parent]);

            $taxonomy = $this->itemDatas()->pull('termdata.taxonomy', '');

            $res = wp_update_term($id, $taxonomy, $this->itemDatas('termdata', []));

            if (!$res instanceof WP_Error) {
                $this->importer()->addWpTerm(
                    $res['term_id'], $item->term_id, $this->withCache ? $item->toArray() : []
                );

                $this->message()->success(sprintf(
                    __('%s > SUCCES: Mise à jour du terme de taxonomie [#%d - %s] depuis [#%d].', 'tify'),
                    $this->getCounter(), $res['term_id'], $item->name, $item->term_id
                ));

                return ['insert_id' => $res['term_id'], 'update' => true];
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Mise à jour le terme de taxonomie [#%d] depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $id, $item->term_id, $item->name, $res->get_error_message(), $item->toJson()
                ));
            }
        } else {
            $this->parseTermdata($item, ['parent' => $parent]);

            $taxonomy = $this->itemDatas()->pull('termdata.taxonomy', '');

            $res = wp_insert_term($item->name, $taxonomy, $this->itemDatas('termdata', []));

            if (!$res instanceof WP_Error && !empty($res['term_id'])) {
                $this->importer()->addWpTerm(
                    $res['term_id'], $item->term_id, $this->withCache ? $item->toArray() : []
                );

                $this->message()->success(sprintf(
                    __('%s > SUCCES: Création du terme de taxonomie [#%d - %s] depuis [#%d].'),
                    $this->getCounter(), $res['term_id'], $item->name, $item->term_id
                ));

                return ['insert_id' => $res['term_id'], 'update' => false];
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Création du terme de taxonomie depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $item->term_id, $item->name, $res->get_error_message(), $item->toJson()
                ));
            }
        }
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
     * Récupération du nom de qualification de la taxonomie d'enregistrement (sortie).
     *
     * @return string
     */
    public function getOutTaxonomy(): ?string
    {
        return $this->outTaxonomy;
    }

    /**
     * @inheritDoc
     */
    public function getQueryArgs(): array
    {
        $args = [];

        if ($this->isHierarchical()) {
            $args['parent'] = 0;
        }

        return $this->queryArgs = array_merge($args, $this->queryArgs, [
            'taxonomy' => $this->getInTaxonomy(),
        ]);
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
     * @param TermTaxonomyModel $item
     * @param array $attrs Liste des attributs personnalisés.
     *
     * @return static
     */
    public function parseTermdata(TermTaxonomyModel $item, array $attrs = []): self
    {
        $this->itemDatas([
            'termdata' => array_merge([
                'description' => $item->description,
                'name'        => $item->name,
                'parent'      => $item->parent,
                'slug'        => $item->slug,
                'taxonomy'    => $this->getOutTaxonomy() ?: $item->taxonomy,
            ], $attrs),
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
