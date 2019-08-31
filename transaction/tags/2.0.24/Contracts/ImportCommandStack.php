<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Contracts;

use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * @mixin BaseCommand
 */
interface ImportCommandStack
{
    /**
     * Ajout d'un nom de qualification à la liste des commandes à exécuter.
     *
     * @param string $name Nom de qualification de la commande.
     *
     * @return static
     */
    public function addStack(string $name): ImportCommandStack;

    /**
     * Récupération de la liste des noms de qualification des commandes à excécuter.
     *
     * @return string[]
     */
    public function getStack(): array;

    /**
     * Définition de la liste des arguments par défaut passés à toutes les commandes lors de leur exécution.
     *
     * @param array $args Liste des arguments par défaut.
     *
     * @return static
     */
    public function setDefaultArgs(array $args): ImportCommandStack;

    /**
     * Définition de la liste des arguments passés à une commande lors de son exécution.
     *
     * @param string $name Nom de qualification de la commande.
     * @param array $args Liste des arguments à exécuter.
     *
     * @return static
     */
    public function setCommandArgs($name, array $args): ImportCommandStack;

    /**
     * Définition d'une liste de noms de qualification de commandes à exécuter.
     *
     * @param string[] $stack
     *
     * @return static
     */
    public function setStack(array $stack): ImportCommandStack;
}