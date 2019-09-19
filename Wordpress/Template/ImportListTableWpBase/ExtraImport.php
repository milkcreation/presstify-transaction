<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpBase;

use tiFy\Plugins\Transaction\Template\ImportListTable\ExtraImport as BaseExtraImport;

class ExtraImport extends BaseExtraImport
{
    /**
     * Instance du gabarit associé.
     * @var Factory
     */
    protected $factory;

    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return array_merge(parent::defaults(), [
            'button' => [
                'tag'     => 'a',
                'attrs'   => [
                    'class' => 'button-primary'
                ],
                'content' => __('Lancer l\'import', 'theme'),
            ],
        ]);
    }
}