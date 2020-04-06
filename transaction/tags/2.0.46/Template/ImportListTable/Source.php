<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Plugins\Parser\{
    Contracts\Reader as ReaderContract,
    Template\FileListTable\Contracts\Source as SourceContract,
    Template\FileListTable\Source as BaseSource,
};

class Source extends BaseSource
{
    /**
     * Instance du gabarit associé.
     * @var Factory
     */
    protected $factory;

    /**
     * @inheritDoc
     */
    public function setReader(?ReaderContract $reader = null): SourceContract
    {
        if (is_null($reader)) {
            $reader = $this->factory->recorder()->fetch()->reader();
        }

        $this->reader = $reader;

        return $this;
    }
}