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

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    #[ORM\Column]
    private ?\DateTime $date_creation = null;

    /**
     * @var Collection<int, Demande>
     */
<<<<<<< HEAD
    #[ORM\OneToMany(targetEntity: Demande::class, mappedBy: 'id_sa')]
    private Collection $demandes;

    #[ORM\OneToOne(mappedBy: 'SA', cascade: ['persist', 'remove'])]
    private ?Salle $salle = null;
=======
    #[ORM\OneToMany(targetEntity: Demande::class, mappedBy: 'systemeAcquisition')]
    private Collection $demandes;

    #[ORM\OneToOne(inversedBy: 'id_sa', cascade: ['persist', 'remove'])]
    private ?Salle $relation = null;
>>>>>>> 6d65c56032ec552fcfc6d6389c77cc76e5f16de4

    public function __construct()
    {
        $this->demandes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateCreation(): ?\DateTime
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTime $date_creation): static
    {
        $this->date_creation = $date_creation;

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
<<<<<<< HEAD
            $demande->setIdSa($this);
=======
            $demande->setSystemeAcquisition($this);
>>>>>>> 6d65c56032ec552fcfc6d6389c77cc76e5f16de4
        }

        return $this;
    }

    public function removeDemande(Demande $demande): static
    {
        if ($this->demandes->removeElement($demande)) {
            // set the owning side to null (unless already changed)
<<<<<<< HEAD
            if ($demande->getIdSa() === $this) {
                $demande->setIdSa(null);
=======
            if ($demande->getSystemeAcquisition() === $this) {
                $demande->setSystemeAcquisition(null);
>>>>>>> 6d65c56032ec552fcfc6d6389c77cc76e5f16de4
            }
        }

        return $this;
    }

<<<<<<< HEAD
    public function getSalle(): ?Salle
    {
        return $this->salle;
    }

    public function setSalle(?Salle $salle): static
    {
        // unset the owning side of the relation if necessary
        if ($salle === null && $this->salle !== null) {
            $this->salle->setSA(null);
        }

        // set the owning side of the relation if necessary
        if ($salle !== null && $salle->getSA() !== $this) {
            $salle->setSA($this);
        }

        $this->salle = $salle;
=======
    public function getRelation(): ?Salle
    {
        return $this->relation;
    }

    public function setRelation(?Salle $relation): static
    {
        $this->relation = $relation;
>>>>>>> 6d65c56032ec552fcfc6d6389c77cc76e5f16de4

        return $this;
    }
}
