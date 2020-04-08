<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Command;

use tiFy\Wordpress\Database\Command\WpBuilderCommand as BaseCommand;
use tiFy\Plugins\Transaction\{Contracts\ImportManager, Proxy\Transaction};

abstract class ImportWpBaseCommand extends BaseCommand
{
    /**
     * Indicateur de mise à jour d'un élément déja importé.
     * @var bool
     */
    protected $updatable = true;

    /**
     * Indicateur d'enregistrement des données d'origine (en cache).
     * @var bool
     */
    protected $withCache = false;

    /**
     * Récupération de l'identifiant de qualification d'un terme de taxonomie depuis son identifiant relationnel.
     *
     * @param string|int $relation Identifiant relationnel.
     *
     * @return int
     */
    public function getRelatedTermId($relation): int
    {
        return $this->importer()->getWpTermId($relation);
    }

    /**
     * Récupération de l'identifiant de qualification d'une publication depuis son identifiant relationnel.
     *
     * @param string|int $relation Identifiant relationnel.
     *
     * @return int
     */
    public function getRelatedPostId($relation): int
    {
        return $this->importer()->getWpPostId($relation);
    }

    /**
     * Récupération de l'identifiant de qualification d'un utilisateur depuis son identifiant relationnel.
     *
     * @param string|int $relation Identifiant relationnel.
     *
     * @return int
     */
    public function getRelatedUserId($relation): int
    {
        return $this->importer()->getWpUserId($relation);
    }

    /**
     * Récupération de l'instance du gestionnaire d'import.
     *
     * @return ImportManager
     */
    public function importer(): ImportManager
    {
        return Transaction::import();
    }

    /**
     * Vérification d'activation de mise à jour d'un élément déjà été importé.
     *
     * @return bool
     */
    public function isUpdatable(): bool
    {
        return $this->updatable;
    }

    /**
     * Définition d'activation de mise à jour des élements déjà été importés.
     *
     * @param bool $update
     *
     * @return static
     */
    public function setUpdatable(bool $update = true): self
    {
        $this->updatable = $update;

        return $this;
    }

    /**
     * Définition de l'enregistrement des données d'origine.
     *
     * @param bool $cache
     *
     * @return static
     */
    public function setWithCache(bool $cache = true): self
    {
        $this->withCache = $cache;

        return $this;
    }
}