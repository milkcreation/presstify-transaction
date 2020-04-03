<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Command;

use Exception;
use Illuminate\Database\Eloquent\{Builder, Collection as BaseCollection, Model as BaseModel};
use Symfony\Component\Console\{Input\InputInterface, Output\OutputInterface};
use tiFy\Wordpress\Database\Model\Post as PostModel;
use WP_Error;

class ImportWpPostCommand extends ImportWpBaseCommand
{
    /**
     * Indicateur de traitement hierarchique.
     * @var bool
     */
    protected $hierachical = true;

    /**
     * Identifiant de qualification du type de post d'origine (entrée).
     * @var string|null
     */
    protected $inPostType;

    /**
     * Identifiant de qualification du type de post d'enregistrement (sortie).
     * @var string|null
     */
    protected $outPostType;

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
            $this->handleCollection($collect, $output, null);
        });

        $this->handleAfter();
    }

    /**
     * Traitement des résultats de requête.
     *
     * @param BaseCollection $collect
     * @param OutputInterface $output
     * @param int $parent
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
                $id = $this->insertOrUpdate(
                    $model, is_null($parent) ? $this->getRelatedPostId($model->post_parent) : $parent
                );

                if ($this->isHierarchical()) {
                    $args = array_merge($this->queryArgs, [
                        'post_type'   => $this->getInPostType(),
                        'post_parent' => $model->post_parent,
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
     * @param PostModel $model
     * @param int $parent
     *
     * @return int
     *
     * @throws Exception
     */
    protected function insertOrUpdate(PostModel $model, int $parent): int
    {
        $this->data()->clear();

        $this->parsePostdata($model, ['post_parent' => $parent]);

        if ($id = $this->getRelatedPostId($model->ID)) {
            $this->data(['ID' => $id]);

            $post_id = wp_update_post($this->data()->all(), true);

            if (!$post_id instanceof WP_Error) {
                $this->importer()->addWpPost($post_id, $model->ID, $this->withCache ? $model->toArray() : []);

                $this->message()->success(sprintf(
                    __('%d -- SUCCES: Mise à jour de la publication [#%d - %s] depuis [#%d].', 'tify'),
                    $this->counter, $post_id, html_entity_decode($model->post_title), $model->ID
                ));

                return $post_id;
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Mise à jour la publication [#%d] depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $id, $model->ID, html_entity_decode($model->post_title), $post_id->get_error_message(), $model->toJson()
                ));
            }
        } else {
            $post_id = wp_insert_post($this->data()->all(), true);

            if (!$post_id instanceof WP_Error) {
                $this->importer()->addWpPost($post_id, $model->ID, $this->withCache ? $model->toArray() : []);

                $this->message()->success(sprintf(
                    __('%d -- SUCCES: Création de la publication [#%d - %s] depuis [#%d].', 'tify'),
                    $this->counter, $post_id, html_entity_decode($model->post_title), $model->ID
                ));

                return $post_id;
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Création de la publication depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $model->ID, html_entity_decode($model->post_title), $post_id->get_error_message(), $model->toJson()
                ));
            }
        }
    }

    /**
     * {@inheritDoc}
     *
     * @return PostModel|Builder
     */
    public function getInModel(): ?BaseModel
    {
        $classname = $this->inModelClassname;

        return ($instance = new $classname()) instanceof PostModel ? $instance : null;
    }

    /**
     * Récupération du nom de qualification du type de post d'origine (entrée).
     *
     * @return string
     */
    public function getInPostType(): ?string
    {
        return $this->inPostType;
    }

    /**
     * {@inheritDoc}
     *
     * @return PostModel|Builder
     */
    public function getOutModel(): ?BaseModel
    {
        $classname = $this->outModelClassname;

        return ($instance = new $classname()) instanceof PostModel ? $instance : null;
    }

    /**
     * Récupération du nom de qualification du type de post d'enregistrement (sortie).
     *
     * @return string
     */
    public function getOutPostType(): ?string
    {
        return $this->outPostType;
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
     * Traitement des données de post à enregistrer (sortie) selon le modèle d'origine (entrée).
     *
     * @param PostModel $model
     * @param array $attrs Liste des données personnalisées.
     *
     * @return static
     */
    public function parsePostdata(PostModel $model, array $attrs = []): self
    {
        $this->data(array_merge([
            'post_date'             => (string)$model->post_date,
            'post_date_gmt'         => (string)$model->post_date_gmt,
            'post_content'          => $model->post_content,
            'post_title'            => $model->post_title,
            'post_excerpt'          => $model->post_excerpt,
            'post_status'           => $model->post_status,
            'comment_status'        => $model->comment_status,
            'ping_status'           => $model->ping_status,
            'post_password'         => $model->post_password,
            'post_name'             => $model->post_name,
            'to_ping'               => $model->to_ping,
            'pinged'                => $model->pinged,
            'post_modified'         => (string)$model->post_modified,
            'post_modified_gmt'     => (string)$model->post_modified_gmt,
            'post_content_filtered' => $model->post_content_filtered,
            'post_parent'           => $model->post_parent,
            'menu_order'            => $model->menu_order,
            'post_type'             => $this->getOutPostType() ?: $model->post_type,
            'post_mime_type'        => $model->post_mime_type,
        ], $attrs));

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return ImportWpPostCommand
     */
    public function parseQueryArgs(): ImportWpBaseCommand
    {
        $args = [];

        if ($this->isHierarchical()) {
            $args['post_parent'] = 0;
        }

        $this->queryArgs = array_merge($args, $this->queryArgs, [
            'post_type' => $this->getInPostType(),
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
     * Définition de l'identifiant de qualification du type de post d'origine (entrée).
     *
     * @param string $post_type
     *
     * @return static
     */
    public function setInPostType(string $post_type): self
    {
        $this->inPostType = $post_type;

        return $this;
    }

    /**
     * Définition de l'identifiant de qualification du type de post d'enregistrement (sortie).
     *
     * @param string $post_type
     *
     * @return static
     */
    public function setOutPostType(string $post_type): self
    {
        $this->outPostType = $post_type;

        return $this;
    }
}