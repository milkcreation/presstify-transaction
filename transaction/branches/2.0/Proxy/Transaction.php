<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Proxy;

use tiFy\Plugins\Transaction\Contracts\{ImportCommand, ImportCommandStack, ImportRecords};
use tiFy\Support\Proxy\AbstractProxy;

/**
 * @method static ImportCommand|null getImportCommand(string $name)
 * @method static ImportCommandStack|null getImportCommandStack(string $name)
 * @method static ImportRecords|null getImportRecords(string $name)
 * @method static ImportCommand|null registerImportCommand(string|null $name = null, ImportRecords|null $records = null, array $params = [])
 * @method static ImportCommandStack|null registerImportCommandStack(string|null $name = null, array $stack = [])
 * @method static ImportRecords|null registerImportRecords(string|null $name = null, array $params = [], ?string $path = null)
 */
class Transaction extends AbstractProxy
{
    public static function getInstanceIdentifier()
    {
        return 'transaction';
    }
}