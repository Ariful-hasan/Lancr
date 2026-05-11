<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\WorkOrder;
use App\Enum\UserRole;
use App\Enum\WorkOrderStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class WorkOrderAcceptTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Clear previous data to ensure a clean state
        $this->entityManager->createQuery('DELETE FROM App\Entity\WorkOrder')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
    }

    /**
     * Case 1: The assigned freelancer accepts a PENDING work order.
     * Expected: Success (200 OK) and status changes to ACTIVE.
     */
    public function testAcceptWorkOrderSuccess(): void
    {
        $clientUser = $this->createUser('client@example.com', UserRole::CLIENT);
        $freelancer = $this->createUser('freelancer@example.com', UserRole::FREELANCER);
        $workOrder = $this->createWorkOrder($clientUser, $freelancer, WorkOrderStatus::PENDING);

        // Simulate being logged in as the assigned freelancer
        $this->client->loginUser($freelancer);

        $this->client->request('POST', sprintf('/api/work-orders/%d/accept', $workOrder->getId()));

        $this->assertResponseIsSuccessful();
        $this->assertResponseFormatSame('json');

        // Verify status changed in the database
        $this->entityManager->refresh($workOrder);
        $this->assertEquals(WorkOrderStatus::ACTIVE, $workOrder->getStatus());
        
        $responseContent = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('active', $responseContent['data']['status']);
        $this->assertEquals('Work order accepted', $responseContent['message']);
    }

    /**
     * Case 2: A different freelancer tries to accept the work order.
     * Expected: 403 Forbidden (handled by Voter).
     */
    public function testAcceptWorkOrderForbiddenForWrongUser(): void
    {
        $clientUser = $this->createUser('client@example.com', UserRole::CLIENT);
        $freelancer = $this->createUser('freelancer@example.com', UserRole::FREELANCER);
        $wrongFreelancer = $this->createUser('wrong@example.com', UserRole::FREELANCER);
        $workOrder = $this->createWorkOrder($clientUser, $freelancer, WorkOrderStatus::PENDING);

        // Simulate being logged in as the WRONG freelancer
        $this->client->loginUser($wrongFreelancer);

        $this->client->request('POST', sprintf('/api/work-orders/%d/accept', $workOrder->getId()));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * Case 3: The freelancer tries to accept an order that is already ACTIVE.
     * Expected: 403 Forbidden (handled by Voter) or 400 Bad Request (if it bypasses Voter to Service).
     * In this project, the Voter also checks the status, so it will be 403.
     */
    public function testAcceptWorkOrderInvalidStatus(): void
    {
        $clientUser = $this->createUser('client@example.com', UserRole::CLIENT);
        $freelancer = $this->createUser('freelancer@example.com', UserRole::FREELANCER);
        
        // Order is already ACTIVE, it cannot be accepted again
        $workOrder = $this->createWorkOrder($clientUser, $freelancer, WorkOrderStatus::ACTIVE);

        $this->client->loginUser($freelancer);

        $this->client->request('POST', sprintf('/api/work-orders/%d/accept', $workOrder->getId()));

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    private function createUser(string $email, UserRole $role): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setName('Test User');
        $user->setPassword('password'); // In a real test, you might use a password hasher
        $user->setRole($role);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createWorkOrder(User $client, User $freelancer, WorkOrderStatus $status): WorkOrder
    {
        $workOrder = new WorkOrder();
        $workOrder->setTitle('Test Work Order');
        $workOrder->setDescription('Description');
        $workOrder->setBudget('1000.00');
        $workOrder->setDeadline(new \DateTimeImmutable('+1 month'));
        $workOrder->setClient($client);
        $workOrder->setFreelancer($freelancer);
        $workOrder->setStatus($status);

        $this->entityManager->persist($workOrder);
        $this->entityManager->flush();

        return $workOrder;
    }
}
