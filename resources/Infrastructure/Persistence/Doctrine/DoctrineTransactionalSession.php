<?php

namespace Resources\Infrastructure\Persistence\Doctrine;

use Resources\TransactionalSession;
use Doctrine\ORM\EntityManager;
use Exception;

class DoctrineTransactionalSession
implements TransactionalSession
{
    /** @var EntityManager */
    protected $em;

    function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function executeAtomically(callable $operation)
    {
        $this->em->getConnection()->beginTransaction();
        try {
            $result = $operation();
            $this->em->commit();
            return $result;
        } catch (\Resources\Exception\RegularException $er) {
            $this->em->rollback();
            $this->em->close();
            throw $er;
        } catch (\Doctrine\DBAL\Exception $dbalEx) {
            $this->em->rollback();
            $this->em->close();
            throw $dbalEx;
        } catch (Exception $ex) {
            $this->em->rollback();
            $this->em->close();
            throw $ex;
        } catch (\Error $error) {
            $this->em->rollback();
            $this->em->close();
            throw $error;
        }
    }
}
