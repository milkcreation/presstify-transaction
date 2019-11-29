<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use tiFy\Template\Templates\ListTable\Actions as BaseActions;
use tiFy\Plugins\Transaction\Template\ImportListTable\Contracts\Actions as ActionsContract;

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
    public function executeImport()
    {
        if ($id = $this->factory->request()->input('id')) {
            if ($item = $this->factory->builder()->getItem($this->factory->request()->input('id'))) {
                $records = $this->factory->records()->executeRecord($item->getOffset());

                return [
                    'success' => true,
                    'data'    => [
                        $records->messages($item->getOffset())->fetch()
                    ]
                ];
            } else {
                return [
                    'success' => false,
                    'data'    => __('Impossible de récupérer l\'élément associé.', 'tify')
                ];
            }
        } elseif ($this->factory->request()->has('idx')) {
            $offset = $this->factory->request()->get('idx');
            $records = $this->factory->records()->executeRecord($offset);

            return [
                'success' => true,
                'data'    => [
                    $offset//$records->messages($offset)->fetch()
                ]
            ];
        }
        return [
            'success' => false,
            'data'    => 'erreur lors de l\import.'
        ];
    }
}