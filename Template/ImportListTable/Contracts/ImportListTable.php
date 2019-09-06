<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable\Contracts;

use tiFy\Plugins\Parser\Template\FileListTable\Contracts\FileListTable;
use tiFy\Plugins\Transaction\Contracts\ImportRecords;

interface ImportListTable extends FileListTable
{
    /**
     * @inheritDoc
     */
    public function records(): ImportRecords;
}