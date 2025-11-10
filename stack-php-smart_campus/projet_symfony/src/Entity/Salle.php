<?php

namespace App\Entity;

use App\Repository\SalleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalleRepository::class)]
class Salle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nom_salle = null;

    #[ORM\Column]
    private ?int $etage = null;

    #[ORM\Column]
    private ?int $Nb_fenetres = null;

    #[ORM\Column]
    private ?\DateTime $date_creation = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $date_modification = null;

    #[ORM\OneToOne(mappedBy: 'salle', cascade: ['persist', 'remove'])]
    private ?SystemeAcquisition $systemeAcquisition = null;

    #[ORM\ManyToOne(inversedBy: 'id_salle')]
    private ?Demande $demande = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomSalle(): ?string
    {
        return $this->nom_salle;
    }

    public function setNomSalle(string $nom_salle): static
    {
        $this->nom_salle = $nom_salle;

        return $this;
    }

    public function getEtage(): ?int
    {
        return $this->etage;
    }

    public function setEtage(int $etage): static
    {
        $this->etage = $etage;

        return $this;
    }

    public function getNbFenetres(): ?int
    {
        return $this->Nb_fenetres;
    }

    public function setNbFenetres(int $Nb_fenetres): static
    {
        $this->Nb_fenetres = $Nb_fenetres;

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

    public function getDateModification(): ?\DateTime
    {
        return $this->date_modification;
    }

    public function setDateModification(?\DateTime $date_modification): static
    {
        $this->date_modification = $date_modification;

        return $this;
    }

    public function getIdSa(): ?SystemeAcquisition
    {
        return $this->id_sa;
    }

    public function setIdSa(?SystemeAcquisition $id_sa): static
    {
        // unset the owning side of the relation if necessary
        if ($id_sa === null && $this->id_sa !== null) {
            $this->id_sa->setRelation(null);
        }

        // set the owning side of the relation if necessary
        if ($id_sa !== null && $id_sa->getRelation() !== $this) {
            $id_sa->setRelation($this);
        }

        $this->id_sa = $id_sa;

        return $this;
    }

    public function getDemande(): ?Demande
    {
        return $this->demande;
    }

    public function setDemande(?Demande $demande): static
    {
        $this->demande = $demande;

        return $this;
    }
}
