<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\WorkOrder;
use App\Repository\Contracts\WorkOrderRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<WorkOrder>
 */
class WorkOrderRepository extends ServiceEntityRepository implements WorkOrderRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WorkOrder::class);
    }

    public function save(WorkOrder $workOrder): void
    {
        $this->getEntityManager()->persist($workOrder);
        $this->getEntityManager()->flush();
    }

    public function delete(WorkOrder $workOrder): void
    {
        $this->getEntityManager()->remove($workOrder);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id): ?WorkOrder
    {
        return $this->find($id);
    }

    public function findByClient(User $client): array
    {
        return $this->createQueryBuilder('wo')
            ->where('wo.client = :client')
            ->setParameter('client', $client)
            ->orderBy('wo.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByFreelancer(User $freelancer): array
    {
        return $this->createQueryBuilder('wo')
            ->where('wo.freelancer = :freelancer')
            ->setParameter('freelancer', $freelancer)
            ->orderBy('wo.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getTotalAllocatedAmount(WorkOrder $workOrder): string
    {
        return (string) ($this->getEntityManager()
            ->createQuery('SELECT SUM(m.amount) FROM App\Entity\Milestone m WHERE m.workOrder = :workOrder')
            ->setParameter('workOrder', $workOrder)
            ->getSingleScalarResult() ?? '0.00');
    }

    public function getTotalPaidAmount(WorkOrder $workOrder): string
    {
        return (string) ($this->getEntityManager()
            ->createQuery('SELECT SUM(m.amount) FROM App\Entity\Milestone m WHERE m.workOrder = :workOrder AND m.status = :status')
            ->setParameter('workOrder', $workOrder)
            ->setParameter('status', \App\Enum\MilestoneStatus::APPROVED)
            ->getSingleScalarResult() ?? '0.00');
    }

    //    /**
    //     * @return WorkOrder[] Returns an array of WorkOrder objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('w.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?WorkOrder
    //    {
    //        return $this->createQueryBuilder('w')
    //            ->andWhere('w.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
