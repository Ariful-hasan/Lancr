<?php

namespace App\Entity;

use App\Enum\PaymentStatus;
use App\Repository\PaymentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PaymentRepository::class)]
#[ORM\Table(name: 'payments')]
#[ORM\Index(name: 'idx_payment_status', columns: ['status'])]
#[ORM\Index(name: 'idx_payment_work_order', columns: ['work_order_id'])]
#[ORM\HasLifecycleCallbacks]
class Payment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['payment:read'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: 'Amount is required')]
    #[Assert\Positive(message: 'Amount must be positive')]
    #[Groups(['payment:read'])]
    private ?string $amount = null;

    #[ORM\Column(type: Types::SMALLINT, options: ['unsigned' => true, 'default' => 0])]
    #[Assert\Choice(choices: [PaymentStatus::PENDING, PaymentStatus::PAID], message: 'Invalid payment status')]
    private ?PaymentStatus $status = PaymentStatus::PENDING;

    #[ORM\Column(nullable: true)]
    #[Groups(['payment:read'])]
    private ?\DateTimeImmutable $paidAt = null;

    #[ORM\Column]
    #[Groups(['payment:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    // ─── Relationships ────────────────────────────────────────────────────────
    #[ORM\ManyToOne(targetEntity: WorkOrder::class)]
    #[ORM\JoinColumn(name: 'work_order_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['payment:read'])]
    private ?WorkOrder $workOrder = null;

    #[ORM\OneToOne(targetEntity: Milestone::class, inversedBy: 'payment', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(name: 'milestone_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['payment:read'])]
    private Milestone $milestone;

    // ─── Lifecycle Callbacks ──────────────────────────────────────────────────
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // ─── Virtual Properties ───────────────────────────────────────────────────
    #[Groups(['payment:read'])]
    public function getStatusLabel(): string
    {
        return $this->status->label();
    }

    // ─── Getters & Setters ────────────────────────────────────────────────────
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    public function getStatus(): ?PaymentStatus
    {
        return $this->status;
    }

    public function setStatus(PaymentStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPaidAt(): ?\DateTimeImmutable
    {
        return $this->paidAt;
    }

    public function setPaidAt(?\DateTimeImmutable $paidAt): static
    {
        $this->paidAt = $paidAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getWorkOrder(): ?WorkOrder
    {
        return $this->workOrder;
    }

    public function setWorkOrder(?WorkOrder $workOrder): static
    {
        $this->workOrder = $workOrder;

        return $this;
    }

    public function getMilestone(): ?Milestone
    {
        return $this->milestone;
    }

    public function setMilestone(?Milestone $milestone): static
    {
        $this->milestone = $milestone;

        return $this;
    }
}
