<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Contracts;

use tiFy\Contracts\{Log\Logger, Support\Collection, Support\LabelsBag, Support\MessagesBag, Support\ParamsBag};
use tiFy\Plugins\Parser\{
    Contracts\Reader,
    Exceptions\ReaderException
};

interface ImportRecords extends Collection
{
    /**
     * Création d'une instance de la classe basée sur un chemin vers un fichier.
     *
     * @param string $path
     * @param array $params Liste des paramètres.
     *
     * @return static
     *
     * @throws ReaderException
     */
    public static function createFromPath(string $path, $params = []): ImportRecords;

    /**
     * Création d'un instance de la classe.
     *
     * @param Reader $reader
     * @param array $params Liste des paramètres.
     *
     * @return static
     */
    public static function createFromReader(Reader $reader, $params = []): ImportRecords;

    /**
     * Execution des fonctions de rappel à l'issue du traitement de l'import.
     *
     * @return static
     */
    public function callAfter(): ImportRecords;

    /**
     * Execution des fonctions de rappel à l'issue du traitement de l'import d'un élément.
     *
     * @param ImportRecord $record Instance de l'élément.
     * @param string|int $key Clé d'indice de l'élément.
     *
     * @return static
     */
    public function callAfterItem(ImportRecord $record, $key): ImportRecords;

    /**
     * Execution des fonctions de rappel au démarrage du traitement de l'import.
     *
     * @return static
     */
    public function callBefore(): ImportRecords;

    /**
     * Execution des fonctions de rappel au démarrage  du traitement de l'import d'un élément.
     *
     * @param ImportRecord $record Instance de l'élément.
     * @param string|int $key Clé d'indice de l'élément.
     *
     * @return static
     */
    public function callBeforeItem(ImportRecord $record, $key): ImportRecords;

    /**
     * Traitement de l'import de la liste des éléments.
     *
     * @return static
     */
    public function execute(): ImportRecords;

    /**
     * Traitement de l'import d'un élément.
     *
     * @param int|string $key Clé d'indice de l'élément.
     *
     * @return static
     */
    public function executeRecord($key): ImportRecords;

    /**
     * Retrouve la liste des éléments.
     *
     * @return static
     */
    public function fetch(): ImportRecords;

    /**
     * Définition du chemin de récupération des enregistrements.
     *
     * @param string $path Chemin absolu
     *
     * @return static
     *
     * @throws ReaderException
     */
    public function fromPath(string $path): ImportRecords;

    /**
     * @inheritDoc
     */
    public function get($key): ?ImportRecord;

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
     * Récupération de l'instance du gestionnaire de transaction.
     *
     * @return Transaction|null
     */
    public function manager(): ?Transaction;

    /**
     * Récupération de la liste des messages de notifications associés au traitement d'un enregistrement.
     *
     * @param int|string $key Indice de qualification de l'élément.
     *
     * @return MessagesBag|null
     */
    public function messages($key): ?MessagesBag;

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
     * Récupération de l'instance du gestionnaires d'enregistrements.
     *
     * @return Reader|null
     */
    public function reader(): ?Reader;

    /**
     * Définition d'une fonction de rappel à l'issue du traitement de l'import.
     *
     * @param callable $func
     *
     * @return static
     */
    public function setAfter(callable $func): ImportRecords;

    /**
     * Définition d'une fonction de rappel à l'issue du traitement de l'import d'un élément.
     *
     * @param callable $func
     *
     * @return static
     */
    public function setAfterItem(callable $func): ImportRecords;

    /**
     * Définition d'une fonction de rappel au démarrage du traitement de l'import.
     *
     * @param callable $func
     *
     * @return static
     */
    public function setBefore(callable $func): ImportRecords;

    /**
     * Définition d'une fonction de rappel au démarrage du traitement de l'import d'un élément.
     *
     * @param callable $func
     *
     * @return static
     */
    public function setBeforeItem(callable $func): ImportRecords;

    /**
     * Définition du gestionnaire d'intitulés.
     *
     * @param LabelsBag $labels
     *
     * @return static
     */
    public function setLabels(LabelsBag $labels): ImportRecords;

    /**
     * Définition du nombre d'enregistrements à traiter lors de l'import.
     *
     * @param int $length
     *
     * @return static
     */
    public function setLength(int $length): ImportRecords;

    /**
     * Définition du gestionnaire de journalisation.
     *
     * @param Logger $logger
     *
     * @return static
     */
    public function setLogger(Logger $logger): ImportRecords;

    /**
     * Définition de l'instance du gestionnaire de transaction.
     *
     * @param Transaction $manager
     *
     * @return static
     */
    public function setManager(Transaction $manager): ImportRecords;

    /**
     * Définition de l'enregistrement de démarrage lors du traitement de l'import.
     *
     * @param int $offset
     *
     * @return static
     */
    public function setOffset(int $offset): ImportRecords;

    /**
     * Définition de la liste des paramètres.
     *
     * @param array $params
     *
     * @return static
     *
     * @throws ReaderException
     */
    public function setParams(array $params): ImportRecords;

    /**
     * Définition de l'instance du lecteur d'enregistrements.
     *
     * @param Reader $reader
     *
     * @return static
     */
    public function setReader(Reader $reader): ImportRecords;

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
     * Retourne la liste des enregistrements sous forme de tableau.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Traitement d'un enregistrement.
     *
     * @param array|ImportRecord $value Valeur de l'enregistrement.
     * @param string|int|null $key Clé d'indice de l'enregistrement.
     *
     * @return ImportRecord
     */
    public function walk($value, $key = null): ImportRecord;
}