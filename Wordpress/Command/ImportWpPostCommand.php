<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Command;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as BaseCollection;
use Symfony\Component\Console\Output\OutputInterface;
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
     */
    public function countQuery(): Builder
    {
        if ($this->isHierarchical() && !$this->getContraintIds()) {
            $args = $this->getQueryArgs();
            unset($args['post_parent']);

            return $this->getBuilder()->where($args);
        } else {
            return parent::countQuery();
        }
    }

    /**
     * {@inheritDoc}
     *
     * @param BaseCollection|PostModel[] $items
     * @param OutputInterface $output
     * @param int $parent
     */
    public function handleItems(BaseCollection $items, OutputInterface $output, ?int $parent = null): void
    {
        foreach ($items as $item) {
            $this->itemDatas()->clear();

            $this->counter++;

            $this->handleItemBefore($item);

            try {
                $id = $this->insertOrUpdate(
                    $item, is_null($parent) ? $this->getRelatedPostId($item->post_parent) : $parent
                );

                if ($this->isHierarchical()) {
                    $args = array_merge($this->getQueryArgs(), [
                        'post_type'   => $this->getInPostType(),
                        'post_parent' => $item->post_parent,
                    ]);

                    $this->getBuilder()->where($args)
                        ->chunkById($this->getChunk(), function (BaseCollection $items) use ($output, $id) {
                            $this->handleItems($items, $output, $id);
                        });
                }
            } catch (Exception $e) {
                $this->message()->error($e->getMessage());
            }

            $this->itemDatas()->set(['insert_id' => $id ?? 0]);

            $this->handleItemAfter($item);

            $this->handleMessages($output);
        }
    }

    /**
     * Création ou mise à jour.
     *
     * @param PostModel $item
     * @param int $parent
     *
     * @return int
     *
     * @throws Exception
     */
    public function insertOrUpdate(PostModel $item, int $parent): int
    {
        if ($id = $this->getRelatedPostId($item->ID)) {
            if (!$this->isUpdatable()) {
                throw new Exception(sprintf(
                    __('%s > INFO: La publication a déjà été importée [#%d - %s] depuis [#%d].', 'tify'),
                    $this->getCounter(), $id, html_entity_decode($item->post_title), $item->ID
                ));
            }

            $this->parsePostdata($item, ['ID' => $id, 'post_parent' => $parent]);

            $post_id = wp_update_post($this->itemDatas('postdata', []), true);

            if (!$post_id instanceof WP_Error) {
                $this->importer()->addWpPost($post_id, $item->ID, $this->withCache ? $item->toArray() : []);

                $this->message()->success(sprintf(
                    __('%s > SUCCES: Mise à jour de la publication [#%d - %s] depuis [#%d].', 'tify'),
                    $this->getCounter(), $post_id, html_entity_decode($item->post_title), $item->ID
                ));

                return $post_id;
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Mise à jour la publication [#%d] depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $id, $item->ID, html_entity_decode($item->post_title), $post_id->get_error_message(), $item->toJson()
                ));
            }
        } else {
            $this->parsePostdata($item, ['post_parent' => $parent]);

            $post_id = wp_insert_post($this->itemDatas('postdata', []), true);

            if (!$post_id instanceof WP_Error) {
                $this->importer()->addWpPost($post_id, $item->ID, $this->withCache ? $item->toArray() : []);

                $this->message()->success(sprintf(
                    __('%s > SUCCES: Création de la publication [#%d - %s] depuis [#%d].', 'tify'),
                    $this->getCounter(), $post_id, html_entity_decode($item->post_title), $item->ID
                ));

                return $post_id;
            } else {
                throw new Exception(sprintf(
                    __('ERREUR: Création de la publication depuis [#%d - %s] >> %s - %s.', 'tify'),
                    $item->ID, html_entity_decode($item->post_title), $post_id->get_error_message(), $item->toJson()
                ));
            }
        }
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
     * Récupération du nom de qualification du type de post d'enregistrement (sortie).
     *
     * @return string
     */
    public function getOutPostType(): ?string
    {
        return $this->outPostType;
    }

    /**
     * @inheritDoc
     */
    public function getQueryArgs(): array
    {
        $args = [];

        if ($this->isHierarchical()) {
            $args['post_parent'] = 0;
        }

        return $this->queryArgs = array_merge($args, $this->queryArgs, [
            'post_type' => $this->getInPostType(),
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
     * Traitement des données de post à enregistrer (sortie) selon le modèle d'origine (entrée).
     *
     * @param PostModel $item
     * @param array $attrs Liste des données personnalisées.
     *
     * @return static
     */
    public function parsePostdata(PostModel $item, array $attrs = []): self
    {
        $this->itemDatas(['postdata' => array_merge([
            'post_author'           => $this->getRelatedUserId($item->post_author) ?: 0,
            'post_date'             => (string)$item->post_date,
            'post_date_gmt'         => (string)$item->post_date_gmt,
            'post_content'          => $item->post_content,
            'post_title'            => $item->post_title,
            'post_excerpt'          => $item->post_excerpt,
            'post_status'           => $item->post_status,
            'comment_status'        => $item->comment_status,
            'ping_status'           => $item->ping_status,
            'post_password'         => $item->post_password,
            'post_name'             => $item->post_name,
            'to_ping'               => $item->to_ping,
            'pinged'                => $item->pinged,
            'post_modified'         => (string)$item->post_modified,
            'post_modified_gmt'     => (string)$item->post_modified_gmt,
            'post_content_filtered' => $item->post_content_filtered,
            'post_parent'           => $item->post_parent,
            'menu_order'            => $item->menu_order,
            'post_type'             => $this->getOutPostType() ?: $item->post_type,
            'post_mime_type'        => $item->post_mime_type,
        ], $attrs)]);

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