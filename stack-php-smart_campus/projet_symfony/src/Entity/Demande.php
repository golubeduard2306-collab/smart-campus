<?php

namespace App\Entity;

use App\Repository\DemandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $id_demande = null;

    #[ORM\Column(length: 20)]
    private ?string $type_demande = null;

    #[ORM\Column]
    private ?\DateTime $date_demande = null;

    #[ORM\Column(length: 20)]
    private ?string $status = null;

    /**
     * @var Collection<int, Salle>
     */
    #[ORM\OneToMany(targetEntity: Salle::class, mappedBy: 'demande')]
    private Collection $id_salle;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SystemeAcquisition $systemeAcquisition = null;

    public function __construct()
    {
        $this->id_salle = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdDemande(): ?int
    {
        return $this->id_demande;
    }

    public function setIdDemande(int $id_demande): static
    {
        $this->id_demande = $id_demande;

        return $this;
    }

    public function getTypeDemande(): ?string
    {
        return $this->type_demande;
    }

    public function setTypeDemande(string $type_demande): static
    {
        $this->type_demande = $type_demande;

        return $this;
    }

    public function getDateDemande(): ?\DateTime
    {
        return $this->date_demande;
    }

    public function setDateDemande(\DateTime $date_demande): static
    {
        $this->date_demande = $date_demande;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getSystemeAcquisition(): ?SystemeAcquisition
    {
        return $this->systemeAcquisition;
    }

    public function setSystemeAcquisition(?SystemeAcquisition $systemeAcquisition): static
    {
        $this->systemeAcquisition = $systemeAcquisition;

        return $this;
    }

    /**
     * @return Collection<int, Salle>
     */
    public function getIdSalle(): Collection
    {
        return $this->id_salle;
    }

    public function addIdSalle(Salle $idSalle): static
    {
        if (!$this->id_salle->contains($idSalle)) {
            $this->id_salle->add($idSalle);
            $idSalle->setDemande($this);
        }

        return $this;
    }

    public function removeIdSalle(Salle $idSalle): static
    {
        if ($this->id_salle->removeElement($idSalle)) {
            // set the owning side to null (unless already changed)
            if ($idSalle->getDemande() === $this) {
                $idSalle->setDemande(null);
            }
        }

        return $this;
    }
}
