<?php

namespace App\Entity;

use App\Repository\DemandeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DemandeRepository::class)]
class Demande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20)]
    private ?string $type_demande = null;

    #[ORM\Column]
    private ?\DateTime $date_demande = null;

    #[ORM\Column(length: 20)]
    private ?string $statut = null;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Salle $id_salle = null;

    #[ORM\ManyToOne(inversedBy: 'demandes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SystemeAcquisition $id_sa = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function getIdSalle(): ?Salle
    {
        return $this->id_salle;
    }

    public function setIdSalle(?Salle $id_salle): static
    {
        $this->id_salle = $id_salle;

        return $this;
    }

    public function getIdSa(): ?SystemeAcquisition
    {
        return $this->id_sa;
    }

    public function setIdSa(?SystemeAcquisition $id_sa): static
    {
        $this->id_sa = $id_sa;

        return $this;
    }
}
