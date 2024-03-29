<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Contracts;

use DateTimeZone;
use tiFy\Contracts\{
    Console\Command,
    Support\ParamsBag
};

interface ImportCommand extends Command
{
    /**
     * Récupération de la date au format datetime.
     *
     * @param int|null $time
     * @param DateTimeZone|null $tz
     *
     * @return string
     */
    public function getDate(?int $time = null, ?DateTimeZone $tz = null): string;

    /**
     * Récupération du format d'affichage des dates.
     *
     * @return string
     */
    public function getDateFormat(): string;

    /**
     * Récupération du niveau d'affichage des alertes.
     *
     * @return int
     */
    public function getLevel(): int;

    /**
     * Récupération des messages de sortie ou d'un message de sortie.
     *
     * @param string|array|null $key Clé d'indice du message
     * @param mixed $default Valeur de retour par défaut
     * @param string[] ...$args Liste des arguments dynamique de remplacement
     *
     * @return string|ParamsBag
     */
    public function messages($key = null, string $default = '', ...$args);

    /**
     * Récupération de paramètre|Définition de paramètres|Instance du gestionnaire de paramètre.
     *
     * @param string|array|null $key Clé d'indice du paramètre à récupérer|Liste des paramètre à définir.
     * @param mixed $default Valeur de retour par défaut lorsque la clé d'indice est une chaine de caractère.
     *
     * @return mixed|ParamsBag
     */
    public function params($key = null, $default = null);

    /**
     * Récupération de l'instance du gestionnaire d'enregistrements.
     *
     * @return ImportRecorder|null
     */
    public function recorder(): ?ImportRecorder;

    /**
     * Définition de la liste des paramètres.
     *
     * @param array $params
     *
     * @return static
     */
    public function setParams(array $params): ImportCommand;

    /**
     * Définition de l'instance du gestionnaire d'enregistrements.
     *
     * @param ImportRecorder $recorder
     *
     * @return static
     */
    public function setRecorder(ImportRecorder $recorder): ImportCommand;
}