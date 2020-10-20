<?php declare(strict_types=1);

namespace tiFy\Plugins\Transaction\Wordpress\Template\ExportListTableWpPost;

use tiFy\Wordpress\Template\Templates\PostListTable\Params as BaseParams;

class Params extends BaseParams
{
    /**
     * @inheritDoc
     */
    public function defaults(): array
    {
        return array_merge(parent::defaults(), [
            'extras'       => ['export'],
        ]);
    }
}