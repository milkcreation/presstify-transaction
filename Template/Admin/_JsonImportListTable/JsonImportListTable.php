<?php

namespace tiFy\Components\AdminView\JsonImportListTable;

use tiFy\Components\AdminView\FileImportListTable\FileImportListTable;

class JsonImportListTable extends FileImportListTable
{
    /**
     * Récupération de la réponse.
     *
     * @return object[]
     */
    protected function getResponse()
    {
        $p = $this->parse_query_args();

        if (empty($p['filename'])) :
            return [];
        endif;

        $items = [];
        if ($_items = json_decode(file_get_contents($p['filename']), true)) :
            foreach($_items as $k => $item) :
                if ($this->current_item() && !in_array($k, $this->current_item())) :
                    continue;
                endif;

                $item['_import_row_index'] = $k;
                $items[] = (object)$item;
            endforeach;

            $this->TotalItems = count($_items);
        endif;

        return $items;
    }
}
