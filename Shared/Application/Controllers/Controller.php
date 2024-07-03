<?php

namespace Shared\Application\Controllers;

use Doctrine\ORM\EntityManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as IlluminateController;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewAllListPayload;
use Resources\Domain\TaskPayload\ViewPaginationListPayload;
use Resources\Domain\TaskPayload\ViewSummaryPayload;
use SharedContext\Domain\ValueObject\LabelData;
use function app;

class Controller extends IlluminateController
{

    protected EntityManager $em;

    public function __construct()
    {
        $this->em = app(EntityManager::class);
    }

    //
    protected function buildViewPaginationListPayload(InputRequest|Request $input): ViewPaginationListPayload
    {
        return new ViewPaginationListPayload([
            'keywordSearch' => $input->get('keywordSearch') ?? [],
            'filters' => $input->get('filters') ?? [],
            'cursorLimit' => $input->get('cursorLimit') ?? [],
            'offsetLimit' => $input->get('offsetLimit') ?? [],
        ]);
    }

    protected function buildViewAllListPayload(InputRequest|Request $input): ViewAllListPayload
    {
        return new ViewAllListPayload([
            'keywordSearch' => $input->get('keywordSearch') ?? [],
            'filters' => $input->get('filters') ?? [],
        ]);
    }
    
    protected function buildViewSummaryPayload(InputRequest|Request $input): ViewSummaryPayload
    {
        $searchSchema = [
            'filters' => $input->get('filters'),
        ];
        return new ViewSummaryPayload($searchSchema);
    }

    //
    protected function createLabelData(InputRequest $input): LabelData
    {
        return new LabelData($input->get('name'), $input->get('description'));
    }
}
