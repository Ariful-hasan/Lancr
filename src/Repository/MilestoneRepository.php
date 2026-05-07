<?php

namespace App\Repository;

use App\Entity\Milestone;
use App\Entity\WorkOrder;
use App\Enum\MilestoneStatus;
use App\Repository\Contracts\MilestoneRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Milestone>
 */
class MilestoneRepository extends ServiceEntityRepository implements MilestoneRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Milestone::class);
    }

    public function save(Milestone $milestone): void
    {
        $this->getEntityManager()->persist($milestone);
        $this->getEntityManager()->flush();
    }

    public function delete(Milestone $milestone): void
    {
        $this->getEntityManager()->remove($milestone);
        $this->getEntityManager()->flush();
    }

    public function findById(int $id): ?Milestone
    {
        return $this->find($id);
    }

    public function findByWorkOrder(WorkOrder $workOrder): array
    {
         return $this->createQueryBuilder('m')
            ->where('m.workOrder = :workOrder')
            ->setParameter('workOrder', $workOrder)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findApprovedByWorkOrder(WorkOrder $workOrder): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.workOrder = :workOrder')
            ->andWhere('m.status = :status')
            ->setParameter('workOrder', $workOrder)
            ->setParameter('status', MilestoneStatus::APPROVED)
            ->getQuery()
            ->getResult();
    }

    public function allApproved(WorkOrder $workOrder): bool
    {
        $nonApprovedCount = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.workOrder = :workOrder')
            ->andWhere('m.status != :approvedStatus')
            ->setParameter('workOrder', $workOrder)
            ->setParameter('approvedStatus', MilestoneStatus::APPROVED)
            ->getQuery()
            ->getSingleScalarResult();

        // We also need to ensure there's at least one milestone to avoid
        // considering an empty WorkOrder as fully approved.
        $totalCount = $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->where('m.workOrder = :workOrder')
            ->setParameter('workOrder', $workOrder)
            ->getQuery()
            ->getSingleScalarResult();

        return $totalCount > 0 && $nonApprovedCount === 0;
    }
    //     * @return Milestone[] Returns an array of Milestone objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Milestone
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
