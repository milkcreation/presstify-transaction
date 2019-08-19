<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Contracts;

use DateTimeZone;
use tiFy\Contracts\Support\ParamsBag;

interface ImportCommand
{
    /**
     * Récupération de la date au format datetime.
     *
     * @param string|null $time Date
     * @param DateTimeZone|null $tz
     *
     * @return string
     */
    public function getDatetime(?string $time = null, ?DateTimeZone $tz = null): string;

    /**
     * Récupération de l'instance du controleur d'import.
     *
     * @return ImportManager|null
     */
    public function getManager(): ?ImportManager;

    /**
     * Récupération des messages de sortie ou d'un message de sortie.
     *
     * @param string|array|null $key Clé d'indice du message
     * @param mixed $default Valeur de retour par défaut
     *
     * @return string|ParamsBag
     */
    public function message($key = null, string $default = '');

    /**
     * Action lancée à l'issue du traitement.
     *
     * @param array $results
     *
     * @return void
     */
    public function onEnd(array $results): void;

    /**
     * Action lancée avant le traitement d'un élément d'import.
     *
     * @param ImportFactory $item Instance de l'élément d'import.
     * @param string|int $key Indice de l'élément d'import.
     *
     * @return void
     */
    public function onItemEnd(ImportFactory $item, $key): void;

    /**
     * Action lancée avant le traitement d'un élément d'import.
     *
     * @param ImportFactory $item Instance de l'élément d'import.
     * @param string|int $key Indice de l'élément d'import.
     *
     * @return void
     */
    public function onItemStart(ImportFactory $item, $key): void;

    /**
     * Action lancée à l'issue du traitement.
     *
     * @return void
     */
    public function onStart(): void;
}