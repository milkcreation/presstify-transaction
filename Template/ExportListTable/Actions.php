<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ExportListTable;

use tiFy\Support\DateTime;
use tiFy\Plugins\Parser\Parsers\CsvWriter;
use tiFy\Plugins\Transaction\Template\ExportListTable\Contracts\Actions as ActionsContract;
use tiFy\Template\Templates\ListTable\Actions as BaseActions;

class Actions extends BaseActions implements ActionsContract
{
    /**
     * Instance du gabarit associé.
     * @var Factory
     */
    protected $factory;

    /**
     * @inheritDoc
     */
    public function executeExport()
    {
        $this->factory->builder()->setPerPage(-1);

        $this->factory->prepare();

        $csv = CsvWriter::createFromPath(null);

        $head = [];
        foreach($this->factory->columns() as $column) {
            $head[] = $column->getTitle();
        }
        $csv->addRow($head);

        foreach($this->factory->items() as $item) {
            $row = [];
            foreach($this->factory->columns() as $column) {
                $row[] = (string)$column;
            }
            $csv->addRow($row);
        }

        $date = DateTime::now(DateTime::getGlobalTimeZone())->format('ymd-His');

        return $csv->download("{$date}-commandes.csv");
    }
}