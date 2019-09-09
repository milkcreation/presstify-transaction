<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Contracts;

use Symfony\Component\Console\Application as ConsoleApplication;
use tiFy\Contracts\Support\Manager;
use tiFy\Plugins\Parser\Exceptions\ReaderException;

interface Transaction extends Manager
{
    /**
     * @inheritDoc
     */
    public function getConsoleApp(): ConsoleApplication;

    /**
     * Récupération de l'instance d'une application d'import.
     *
     * @param string $name Nom de qualification de la commande.
     *
     * @return ImportCommand|null
     */
    public function getImportCommand(string $name): ?ImportCommand;

    /**
     * Récupération de l'instance d'un jeux d'applications d'import.
     *
     * @param string $name Nom de qualification du jeu.
     *
     * @return ImportCommand|null
     */
    public function getImportCommandStack(string $name): ?ImportCommandStack;

    /**
     * Récupération de l'instance de traitement des enregistrements.
     *
     * @param string $name Nom de qualification du jeu.
     *
     * @return ImportCommand|null
     */
    public function getImportRecords(string $name): ?ImportRecords;

    /**
     * Définition d'une instance d'application d'import.
     *
     * @param string|null $name Nom de qualification de la commande.
     * @param ImportRecords $records Instance de traitement des enregistrements.
     * @param array $params Liste des paramètres de configuration de la commande.
     *
     * @return ImportCommand|null
     */
    public function registerImportCommand(
        ?string $name = null,
        ?ImportRecords $records = null,
        array $params = []
    ): ?ImportCommand;

    /**
     * Définition d'une instance d'un jeux d'applications d'import.
     *
     * @param string|null $name Nom de qualification de la commande.
     * @param string[] $stack Liste des nom de qualification des commandes associées.
     *
     * @return ImportCommandStack|null
     */
    public function registerImportCommandStack(?string $name = null, array $stack = []): ?ImportCommandStack;

    /**
     * Définition d'une instance de traitement des enregistrements.
     *
     * @param string $name Nom de qualification du jeu.
     * @param array $params Liste des paramètres de configuration de la commande.
     *
     * @return ImportCommand|null
     *
     * @throws ReaderException
     */
    public function registerImportRecords(string $name, array $params = []): ?ImportRecords;

    /**
     * Récupération du chemin absolu vers le répertoire des ressources.
     *
     * @param string|null $path Chemin relatif du sous-repertoire.
     *
     * @return string
     */
    public function resourcesDir(string $path = null): string;

    /**
     * Récupération de l'url absolue vers le répertoire des ressources.
     *
     * @param string|null $path Chemin relatif du sous-repertoire.
     *
     * @return string
     */
    public function resourcesUrl(string $path = null): string;
}
