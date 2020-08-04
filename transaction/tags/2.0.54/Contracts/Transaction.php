<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Contracts;

use Exception;
use Psr\Container\ContainerInterface as Container;
use tiFy\Contracts\Console\Console;

interface Transaction
{
    /**
     * Récupération de l'instance du gestionnaire d'application CLI.
     *
     * @return Console
     */
    public function getConsole(): Console;

    /**
     * Récupération de l'instance du conteneur d'injection de dépendances.
     *
     * @return Container|null
     */
    public function getContainer(): ?Container;

    /**
     * Récupération du chemin absolu vers le répertoire des ressources.
     *
     * @param string|null $path Chemin relatif d'une resource (répertoire|fichier).
     *
     * @return string
     */
    public function dir(string $path = null): string;

    /**
     * Récupération de l'instance du gestionnaire d'import.
     *
     * @return ImportManager|null
     */
    public function import(): ?ImportManager;

    /**
     * Résolution de service.
     *
     * @param string $alias Nom de qualification du service
     *
     * @return object|mixed|null
     *
     * @throws Exception
     */
    public function resolve(string $alias): ?object;

    /**
     * Définition de l'instance du conteneur d'injection de dépendances.
     *
     * @param Container $container
     *
     * @return static
     */
    public function setContainer(Container $container): Transaction;

    /**
     * Récupération de l'url absolue vers le répertoire des ressources.
     *
     * @param string|null $path Chemin relatif d'une resource (répertoire|fichier).
     *
     * @return string
     */
    public function url(string $path = null): string;
}
