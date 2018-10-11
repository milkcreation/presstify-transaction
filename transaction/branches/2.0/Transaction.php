<?php

/**
 * @name Transaction
 * @desc Gestion des données de transaction.
 * @author Jordy Manner <jordy@milkcreation.fr>
 * @package presstify-plugins/transaction
 * @namespace \tiFy\Plugins\Transaction
 * @version 2.0.0
 */

namespace tiFy\Plugins\Transaction;

/**
 * Class Transaction
 * @package tiFy\Plugins\Transaction
 *
 * Activation :
 * ----------------------------------------------------------------------------------------------------
 * Dans config/app.php ajouter \tiFy\Plugins\Transaction\TransactionServiceProvider à la liste des fournisseurs de services chargés automatiquement par l'application.
 * ex.
 * <?php
 * ...
 * use tiFy\Plugins\Transaction\TransactionServiceProvider;
 * ...
 *
 * return [
 *      ...
 *      'providers' => [
 *          ...
 *          TransactionServiceProvider::class
 *          ...
 *      ]
 * ];
 *
 * Configuration :
 * ----------------------------------------------------------------------------------------------------
 * Dans le dossier de config, créer le fichier transaction.php
 * @see /vendor/presstify-plugins/transaction/Resources/config/transaction.php Exemple de configuration
 */
final class Transaction
{
    use TransactionResolverTrait;
}
