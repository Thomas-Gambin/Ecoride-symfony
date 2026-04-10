<?php

declare(strict_types=1);

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Attribute as ODM;

#[ODM\EmbeddedDocument]
class TripSnapshot
{
    #[ODM\Field(type: 'string')]
    private string $departureDate = '';

    #[ODM\Field(type: 'string')]
    private string $departureTime = '';

    #[ODM\Field(type: 'string')]
    private string $departureLocation = '';

    #[ODM\Field(type: 'string')]
    private string $arrivalDate = '';

    #[ODM\Field(type: 'string')]
    private string $arrivalTime = '';

    #[ODM\Field(type: 'string')]
    private string $arrivalLocation = '';

    public function getDepartureDate(): string
    {
        return $this->departureDate;
    }

    public function setDepartureDate(string $departureDate): self
    {
        $this->departureDate = $departureDate;

        return $this;
    }

    public function getDepartureTime(): string
    {
        return $this->departureTime;
    }

    public function setDepartureTime(string $departureTime): self
    {
        $this->departureTime = $departureTime;

        return $this;
    }

    public function getDepartureLocation(): string
    {
        return $this->departureLocation;
    }

    public function setDepartureLocation(string $departureLocation): self
    {
        $this->departureLocation = $departureLocation;

        return $this;
    }

    public function getArrivalDate(): string
    {
        return $this->arrivalDate;
    }

    public function setArrivalDate(string $arrivalDate): self
    {
        $this->arrivalDate = $arrivalDate;

        return $this;
    }

    public function getArrivalTime(): string
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(string $arrivalTime): self
    {
        $this->arrivalTime = $arrivalTime;

        return $this;
    }

    public function getArrivalLocation(): string
    {
        return $this->arrivalLocation;
    }

    public function setArrivalLocation(string $arrivalLocation): self
    {
        $this->arrivalLocation = $arrivalLocation;

        return $this;
    }
}
