<?php

namespace App\Http\Controllers;

use Doctrine\ORM\EntityManager;
use Illuminate\Routing\Controller as BaseController;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use SharedContext\Domain\ValueObject\LabelData;
use function app;

class Controller extends BaseController
{

    protected EntityManager $em;

    public function __construct()
    {
        $this->em = app(EntityManager::class);
    }

    //
    protected function buildViewPaginationListPayload(InputRequest $input): ViewPaginationListPayload
    {
        return new ViewPaginationListPayload([
            'keywordSearch' => $input->get('keywordSearch') ?? [],
            'filters' => $input->get('filters') ?? [],
            'cursorLimit' => $input->get('cursorLimit') ?? [],
            'offsetLimit' => $input->get('offsetLimit') ?? [],
        ]);
    }

    //
    protected function createLabelData(InputRequest $input): LabelData
    {
        return new LabelData($input->get('name'), $input->get('description'));
    }
}
