<?php

namespace App\Http\Controllers\CompanyBC\InCompany;

use App\Http\Controllers\CompanyBC\CompanyUserRoleInterface;
use App\Http\Controllers\Controller;
use App\Http\GraphQL\CompanyBC\Object\CustomerObjectInCompanyBC;
use Company\Domain\Model\AreaStructure\Area;
use Company\Domain\Model\AreaStructure\Area\Customer;
use Company\Domain\Model\AreaStructure\Area\CustomerData;
use Company\Domain\Task\InCompany\Customer\AddCustomer;
use Company\Domain\Task\InCompany\Customer\ViewAllCustomer;
use Company\Domain\Task\InCompany\Customer\ViewCustomerDetail;
use Company\Domain\Task\InCompany\Customer\ViewCustomerList;
use Company\Infrastructure\Persistence\Doctrine\Repository\DoctrineCustomerRepository;
use Illuminate\Http\Request;
use League\Csv\Reader;
use League\Csv\Writer;
use Resources\Application\InputRequest;
use Resources\Domain\TaskPayload\ViewDetailPayload;
use Resources\Exception\RegularException;
use Resources\Infrastructure\GraphQL\Attributes\GraphqlMapableController;
use Resources\Infrastructure\GraphQL\Attributes\Query;
use function response;

#[GraphqlMapableController(entity: CustomerObjectInCompanyBC::class)]
class CustomerController extends Controller
{

    protected function repository(): DoctrineCustomerRepository
    {
        return $this->em->getRepository(Customer::class);
    }

    public function importCustomerFromCsv(CompanyUserRoleInterface $user, Request $request)
    {
        $request->validate([
            'file' => 'required|max:2048',
        ]);
        $file = $request->file('file');
        $reader = Reader::createFromFileObject($file->openFile());
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);

        $areaRepository = $this->em->getRepository(Area::class);
        $task = new AddCustomer($this->repository(), $areaRepository);

        $failedImportList = [];
        foreach ($reader->getRecords() as $record) {
            $payload = (new CustomerData())
                    ->setName($record['name'] ?? null)
                    ->setPhone($record['phone'] ?? null)
                    ->setEmail($record['email'] ?? null)
                    ->setSource($record['source'] ?? null);
            try {
                $user->executeTaskInCompany($task, $payload);
            } catch (RegularException $ex) {
                $failedImportList[] = [
                    'name' => $payload->name ?? null,
                    'phone' => $payload->phone ?? null,
                    'email' => $payload->email ?? null,
                    'source' => $payload->source ?? null,
                    'errorDetail' => $ex->getErrorDetail(),
                ];
            }
        }
        return response()->json($failedImportList);
    }

    public function exportCustomerToCsv(CompanyUserRoleInterface $user, Request $request)
    {
        $task = new ViewAllCustomer($this->repository());
        $payload = $this->buildViewAllListPayload($request);
        $user->executeTaskInCompany($task, $payload);

        $headers = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=customer.csv',
            'Expires' => '0',
            'Pragma' => 'public'
        ];
        return response()->stream(function () use ($payload) {
                    $stream = fopen('php://output', 'w');

                    $writer = Writer::createFromStream($stream);
                    $writer->encloseAll();
                    $writer->setDelimiter(';');
                    $writer->insertOne(['name', 'phone', 'email', 'source', 'verificationScore']);
                    $writer->insertAll($payload->result);

                    fclose($stream);
                }, 200, $headers);
    }

    //
    #[Query(responseWrapper: Query::PAGINATION_RESPONSE_WRAPPER)]
    public function customerList(CompanyUserRoleInterface $user, InputRequest $input)
    {
        $task = new ViewCustomerList($this->repository());
        $payload = $this->buildViewPaginationListPayload($input);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }

    #[Query]
    public function customerDetail(CompanyUserRoleInterface $user, string $id)
    {
        $task = new ViewCustomerDetail($this->repository());
        $payload = new ViewDetailPayload($id);

        $user->executeTaskInCompany($task, $payload);
        return $payload->result;
    }
}
