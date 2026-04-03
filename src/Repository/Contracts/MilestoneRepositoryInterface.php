<?php

namespace App\Repository\Contracts;

use App\Entity\Milestone;
use App\Entity\WorkOrder;

interface MilestoneRepositoryInterface
{
    public function save(Milestone $milestone): void;
    public function delete(Milestone $milestone): void;
    public function findById(int $id): ?Milestone;
    public function findByWorkOrder(WorkOrder $workOrder): array;
    public function findApprovedByWorkOrder(WorkOrder $workOrder): array;
    public function allApproved(WorkOrder $workOrder): bool;
}