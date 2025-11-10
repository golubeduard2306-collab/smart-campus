<?php

namespace App\Entity;

use App\Repository\SystemeAcquisitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SystemeAcquisitionRepository::class)]
class SystemeAcquisition
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $date_creation = null;

    #[ORM\Column(length: 19)]
    private ?string $statut = null;

    /**
     * @var Collection<int, Demande>
     */
    #[ORM\OneToMany(targetEntity: Demande::class, mappedBy: 'systemeAcquisition')]
    private Collection $demandes;

    #[ORM\OneToOne(inversedBy: 'id_sa', cascade: ['persist', 'remove'])]
    private ?Salle $relation = null;

    public function __construct()
    {
        $this->demandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTime $date_creation): static
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * @return Collection<int, Demande>
     */
    public function getDemandes(): Collection
    {
        return $this->demandes;
    }

    public function addDemande(Demande $demande): static
    {
        if (!$this->demandes->contains($demande)) {
            $this->demandes->add($demande);
            $demande->setSystemeAcquisition($this);
        }

        return $this;
    }

    public function removeDemande(Demande $demande): static
    {
        if ($this->demandes->removeElement($demande)) {
            // set the owning side to null (unless already changed)
            if ($demande->getSystemeAcquisition() === $this) {
                $demande->setSystemeAcquisition(null);
            }
        }

        return $this;
    }

    public function getRelation(): ?Salle
    {
        return $this->relation;
    }

    public function setRelation(?Salle $relation): static
    {
        $this->relation = $relation;

        return $this;
    }
}
