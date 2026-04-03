<?php

namespace App\Repository\Contracts;

use App\Entity\User;
use App\Entity\WorkOrder;

interface WorkOrderRepositoryInterface
{
    public function save(WorkOrder $workOrder): void;
    public function delete(WorkOrder $workOrder): void;
    public function findById(int $id): ?WorkOrder;
    public function findByClient(User $client): array;
    public function findByFreelancer(User $freelancer): array;
}