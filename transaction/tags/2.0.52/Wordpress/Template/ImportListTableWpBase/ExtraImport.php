<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ImportListTableWpBase;

use tiFy\Plugins\Transaction\Template\ImportListTable\ExtraImport as BaseExtraImport;
use tiFy\Template\Templates\ListTable\Contracts\Extra as BaseExtraContract;

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
    public function parse(): BaseExtraContract
    {
        parent::parse();

        if (is_admin()) {
            if (!$this->get('button.attrs.class')) {
                $this->set('button.attrs.class', 'button-primary');
            }

            if (!$this->get('cancel.attrs.class')) {
                $this->set('cancel.attrs.class', 'button-primary');
            }
        }

        return $this;
    }
}