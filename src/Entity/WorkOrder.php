<?php

namespace App\Entity;

use App\Repository\WorkOrderRepository;
use App\Enum\WorkOrderStatus;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WorkOrderRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Table(name: 'work_orders')]
class WorkOrder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['work_order:read', 'work_order:embed'])]
    private ?int $id = null;

    #[Groups(['work_order:read', 'work_order:embed'])]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Title is required')]
    #[Assert\Length(min: 2, max: 255, maxMessage: 'Title cannot be longer than {{ limit }} characters')]
    private ?string $title = null;

    #[Groups(['work_order:read'])]
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: 1000, maxMessage: 'Description cannot be longer than {{ limit }} characters')]
    private ?string $description = null;

    #[Groups(['work_order:read'])]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: false)]
    #[Assert\NotBlank(message: 'Budget is required')]
    #[Assert\Positive(message: 'Budget must be positive')]
    private ?string $budget = null;

    private float $amountPaid = 0.0;

    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private WorkOrderStatus $status = WorkOrderStatus::DRAFT;

    #[Groups(['work_order:read'])]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: 'Deadline is required')]
    private ?\DateTime $deadline = null;

    #[ORM\Column]
    #[Groups(['work_order:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['work_order:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    // ─── Relationships ────────────────────────────────────────────────────────
    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['work_order:read'])]
    private ?User $client = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'freelancer_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['work_order:read'])]
    private ?User $freelancer = null;

    #[ORM\OneToMany(targetEntity: Milestone::class, mappedBy: 'workOrder', cascade: ['persist', 'remove'])]
    private Collection $milestones;

    public function __construct()
    {
        $this->milestones = new ArrayCollection();
    }

    // ─── Lifecycle Callbacks ──────────────────────────────────────────────────
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBudget(): ?string
    {
        return $this->budget;
    }

    public function setBudget(string $budget): static
    {
        $this->budget = $budget;

        return $this;
    }

    #[Groups(['work_order:read'])]
    public function getAmountPaid(): ?float
    {
        return $this->amountPaid;
    }

    public function setAmountPaid(float $amountPaid): static
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    public function getStatus(): ?WorkOrderStatus
    {
        return $this->status;
    }

    public function setStatus(WorkOrderStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    #[Groups(['work_order:read'])]
    public function getStatusLabel(): string
    {
        return $this->status->label();
    }

    public function getDeadline(): ?\DateTime
    {
        return $this->deadline;
    }

    public function setDeadline(\DateTime $deadline): static
    {
        $this->deadline = $deadline;

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
    
    public function getClient(): ?User
    {
        return $this->client;
    }
    
    public function setClient(User $client): static
    {
        $this->client = $client;
        
        return $this;
    }
    
    public function getFreelancer(): ?User
    {
        return $this->freelancer;
    }
    
    public function setFreelancer(User $freelancer): static
    {
        $this->freelancer = $freelancer;
        
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

    public function getMilestones(): Collection
    {
        return $this->milestones;
    }

    public function addMilestone(Milestone $milestone): static
    {
        if (!$this->milestones->contains($milestone)) {
            $this->milestones->add($milestone);
            $milestone->setWorkOrder($this);
        }

        return $this;
    }

    public function removeMilestone(Milestone $milestone): static
    {
        $this->milestones->removeElement($milestone);
        
        return $this;
    }
}
