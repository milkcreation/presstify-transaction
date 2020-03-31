<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Proxy;

use tiFy\Plugins\Transaction\Contracts\{ImportManager, Transaction as TransactionContract};
use tiFy\Support\Proxy\AbstractProxy;

/**
 * @method static ImportManager import()
 * @method static string dir(string|null $path = null)
 * @method static string url(string|null $path = null)
 */
class Transaction extends AbstractProxy
{
    /**
     * {@inheritDoc}
     *
     * @return TransactionContract
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    /**
     * @inheritDoc
     */
    public static function getInstanceIdentifier()
    {
        return 'transaction';
    }
}