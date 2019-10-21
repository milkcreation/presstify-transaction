<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction;

use tiFy\Console\CommandStack as BaseCommandStack;
use tiFy\Plugins\Transaction\Contracts\ImportCommandStack as ImportCommandStackContract;

class ImportCommandStack extends BaseCommandStack implements ImportCommandStackContract
{

}