<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Support\ParamsBag;
use tiFy\Plugins\Parser\{
    Exceptions\ReaderException,
    Reader
};
use tiFy\Plugins\Transaction\Template\ImportListTable\Contracts\FileBuilder as FileBuilderContract;
use tiFy\Template\Factory\FactoryAwareTrait;
use tiFy\Template\Templates\ListTable\Builder as BaseBuilder;
use tiFy\Template\Templates\ListTable\Contracts\Builder as BaseBuilderContract;

class FileBuilder extends BaseBuilder implements FileBuilderContract
{
    use FactoryAwareTrait;

    /**
     * Instance du gabarit d'affichage.
     * @var ImportListTable
     */
    protected $factory;

    /**
     * Source de récupération de la liste des éléments.
     * @var ParamsBag|null
     */
    protected $source;

    /**
     * @inheritDoc
     */
    public function fetchItems(): BaseBuilderContract
    {
        $this->parse();

        try {
            $reader = Reader::createFromPath(
                (string)$this->getSource('filename'), [
                'page'     => $this->getPage(),
                'per_page' => $this->getPerPage(),
            ]);

            $this->factory->items()->set($reader->all());

            if ($count = $reader->count()) {
                $this->factory->pagination()
                              ->setCount($count)
                              ->setCurrentPage($reader->getPage())
                              ->setPerPage($reader->getPerPage())
                              ->setLastPage($reader->getLastPage())
                              ->setTotal($reader->getTotal())
                              ->parse();
            }
        } catch(ReaderException $e) {
            $this->factory->label(['no_item' => $e->getMessage()]);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSource(?string $key = null, $default = null)
    {
        if (!$this->source instanceof ParamsBag) {
            return $default;
        } elseif (is_null($key)) {
            return $this->source;
        } else {
            return $this->source->get($key, $default);
        }
    }

    /**
     * @inheritDoc
     */
    public function setSource(array $source): BaseBuilderContract
    {
        $this->source = ParamsBag::createFromAttrs($source);

        return $this;
    }
}