<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Template\ImportListTable;

use Exception;
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
            $this->factory->prepare();

            if ($item = $this->factory->builder()->getItem($this->factory->request()->input('id'))) {
                $records = $this->factory->records()->executeRecord($item->getOffset());

                return [
                    'success' => true,
                    'data'    => [
                        $records->messages($item->getOffset())->fetch()
                    ]
                ];
            } else {
                throw new Exception(__('Impossible de récupérer l\'élément associé.', 'tify'));
            }
        } elseif ($this->factory->request()->has('idx')) {
            $this->factory->prepare();

            $offset = $this->factory->request()->get('idx');
            $records = $this->factory->records()->executeRecord($offset);

            return [
                'success' => true,
                'data'    => [
                    $offset//$records->messages($offset)->fetch()
                ]
            ];
        }

        throw new Exception(__('Impossible de récupérer l\'élément associé.', 'tify'));
    }
}