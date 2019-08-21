<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Contracts;

use Psr\Log\LoggerInterface as Logger;
use tiFy\Contracts\{
    Support\LabelsBag,
    Support\ParamsBag
};
use tiFy\Plugins\Parser\Contracts\Reader;

interface ImportManager
{
    /**
     * Création d'une instance de la classe basée sur un chemin vers un fichier.
     *
     * @param string $path
     * @param array $params Liste des paramètre
     *
     * @return static
     */
    public static function createFromPath(string $path, $params = []): ImportManager;

    /**
     * Création d'un instance de la classe.
     *
     * @param Reader $reader
     * @param array $params Liste des paramètre
     *
     * @return static
     */
    public static function createFromReader(Reader $reader, $params = []): ImportManager;

    /**
     * Execution des fonctions de rappel à l'issue du traitement de l'import.
     *
     * @return static
     */
    public function callAfter(): ImportManager;

    /**
     * Execution des fonctions de rappel à l'issue du traitement de l'import d'un élément.
     *
     * @param ImportFactory $item Instance de l'élément.
     * @param string|int $key Clé d'indice de l'élément.
     *
     * @return static
     */
    public function callAfterItem(ImportFactory $item, $key): ImportManager;

    /**
     * Execution des fonctions de rappel au démarrage du traitement de l'import.
     *
     * @return static
     */
    public function callBefore(): ImportManager;

    /**
     * Execution des fonctions de rappel au démarrage  du traitement de l'import d'un élément.
     *
     * @param ImportFactory $item Instance de l'élément.
     * @param string|int $key Clé d'indice de l'élément.
     *
     * @return static
     */
    public function callBeforeItem(ImportFactory $item, $key): ImportManager;

    /**
     * Traitement de l'import de la liste des éléments.
     *
     * @return static
     */
    public function execute(): ImportManager;

    /**
     * Traitement de l'import d'un élément.
     *
     * @param ImportFactory $item Données de l'élément.
     * @param int|string $key Clé d'indice de l'élément.
     *
     * @return static
     */
    public function executeItem(ImportFactory $item, $key): ImportManager;

    /**
     * Récupération de l'enregistrement de démarrage lors du traitement de l'import.
     *
     * @return int
     */
    public function getOffset(): int;

    /**
     * Récupération du nombre d'enregistrements à traiter lors de l'import.
     *
     * @return int|null
     */
    public function getLength(): ?int;

    /**
     * Récupération de la liste des enregistrements.
     *
     * @return ImportFactory[]
     */
    public function getRecords(): array;

    /**
     * Récupération d'intitulé|Définition d'intitulés|Instance du gestionnaire d'intitulés.
     *
     * @param string|array|null $key Clé d'indice de l'intitulé à récupérer|Liste des intitulés à définir.
     * @param mixed $default Valeur de retour par défaut lorsque la clé d'indice est une chaine de caractère.
     *
     * @return mixed|LabelsBag
     */
    public function labels($key = null, $default = null);

    /**
     * Journalisation des événements|Récupération de l'instance du gestionnaire de journalisation.
     *
     * @param int|string|null $level Niveau de notification
     * @param string $message Intitulé du message du journal.
     * @param array $context Liste des éléments de contexte.
     *
     * @return Logger|null
     */
    public function logger($level = null, string $message = '', array $context = []): ?Logger;

    /**
     * Récupération de paramètre|Définition de paramètres|Instance du gestionnaire de paramètre.
     *
     * @param string|array|null $key Clé d'indice du paramètre à récupérer|Liste des paramètres à définir.
     * @param mixed $default Valeur de retour par défaut lorsque la clé d'indice est une chaine de caractère.
     *
     * @return mixed|ParamsBag
     */
    public function params($key = null, $default = null);

    /**
     * Définition d'une fonction de rappel à l'issue du traitement de l'import.
     *
     * @param callable $func
     *
     * @return static
     */
    public function setAfter(callable $func): ImportManager;

    /**
     * Définition d'une fonction de rappel à l'issue du traitement de l'import d'un élément.
     *
     * @param callable $func
     *
     * @return static
     */
    public function setAfterItem(callable $func): ImportManager;

    /**
     * Définition d'une fonction de rappel au démarrage du traitement de l'import.
     *
     * @param callable $func
     *
     * @return static
     */
    public function setBefore(callable $func): ImportManager;

    /**
     * Définition d'une fonction de rappel au démarrage du traitement de l'import d'un élément.
     *
     * @param callable $func
     *
     * @return static
     */
    public function setBeforeItem(callable $func): ImportManager;

    /**
     * Définition du nombre d'enregistrements à traiter lors de l'import.
     *
     * @param int $length
     *
     * @return static
     */
    public function setLength(int $length): ImportManager;

    /**
     * Définition du gestionnaire d'intitulés.
     *
     * @param LabelsBag $labels
     *
     * @return static
     */
    public function setLabels(LabelsBag $labels): ImportManager;

    /**
     * Définition du gestionnaire de journalisation.
     *
     * @param Logger $logger
     *
     * @return static
     */
    public function setLogger(Logger $logger): ImportManager;

    /**
     * Définition de la liste des paramètres.
     *
     * @param array $params
     *
     * @return static
     */
    public function setParams(array $params): ImportManager;

    /**
     * Définition de l'enregistrement de démarrage lors du traitement de l'import.
     *
     * @param int $offset
     *
     * @return static
     */
    public function setOffset(int $offset): ImportManager;

    /**
     * Définition d'un enregistrement.
     *
     * @param string|int|array $key Clé d'indice de l'élément ou liste des éléments à définir.
     * @param mixed $value Valeur de l'élément si la clé d'index est de type string.
     *
     * @return ImportManager
     */
    public function setRecord($key, $value = null): ImportManager;

    /**
     * Récupération d'info|Définition d'infos|Instance du gestionnaire d'information de traitement.
     *
     * @param string|array|null $key Clé d'indice de l'info à récupérer|Liste des infos à définir.
     * @param mixed $default Valeur de retour par défaut lorsque la clé d'indice est une chaine de caractère.
     *
     * @return mixed|ParamsBag
     */
    public function summary($key = null, $default = null);

    /**
     * Traitement d'un enregistrement.
     *
     * @param array|ImportFactory $value Valeur de l'enregistrement.
     * @param string|int|null $key Clé d'indice de l'enregistrement.
     *
     * @return ImportFactory
     */
    public function walkRecord($value, $key = null): ImportFactory;
}