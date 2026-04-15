<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\MilestoneStatus;
use App\Repository\MilestoneRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MilestoneRepository::class)]
#[ORM\Table(name: 'milestones')]
#[ORM\Index(name: 'idx_milestone_work_order', columns: ['work_order_id'])]
#[ORM\Index(name: 'idx_milestone_status', columns: ['status'])]
#[ORM\HasLifecycleCallbacks]
class Milestone
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\Positive(message: 'Amount must be positive')]
    private ?string $amount = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $dueDate = null;

    #[ORM\Column(type: Types::SMALLINT, options: ['unsigned' => true, 'default' => 0], enumType: MilestoneStatus::class)]
    private ?MilestoneStatus $status = MilestoneStatus::PENDING;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reviewNote = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $submittedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $reviewedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    // ─── Relationships ────────────────────────────────────────────────────────
    #[ORM\ManyToOne(targetEntity: WorkOrder::class, inversedBy: 'milestones')]
    #[ORM\JoinColumn(name: 'work_order_id', referencedColumnName: 'id', nullable: false)]
    private ?WorkOrder $workOrder = null;

    #[ORM\OneToOne(targetEntity: Payment::class, mappedBy: 'milestone', cascade: ['persist', 'remove'])]
    private ?Payment $payment = null;

    // ─── Lifecycle Callbacks ──────────────────────────────────────────────────
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ─── Virtual Properties ───────────────────────────────────────────────────

    public function getStatusLabel(): string
    {
        return $this->status->label();
    }

    // ─── Getters & Setters ────────────────────────────────────────────────────
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
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

    public function getDueDate(): ?\DateTimeImmutable
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTimeImmutable $dueDate): static
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getStatus(): ?MilestoneStatus
    {
        return $this->status;
    }

    public function setStatus(MilestoneStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getReviewNote(): ?string
    {
        return $this->reviewNote;
    }

    public function setReviewNote(?string $reviewNote): static
    {
        $this->reviewNote = $reviewNote;

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

    public function getSubmittedAt(): ?\DateTimeImmutable
    {
        return $this->submittedAt;
    }

    public function setSubmittedAt(?\DateTimeImmutable $submittedAt): static
    {
        $this->submittedAt = $submittedAt;

        return $this;
    }

    public function getReviewedAt(): ?\DateTimeImmutable
    {
        return $this->reviewedAt;
    }

    public function setReviewedAt(?\DateTimeImmutable $reviewedAt): static
    {
        $this->reviewedAt = $reviewedAt;

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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPayment(): ?Payment
    {
        return $this->payment;
    }

    public function setPayment(?Payment $payment): static
    {
        $this->payment = $payment;

        return $this;
    }
}
