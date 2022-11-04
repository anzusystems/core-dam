<?php

declare(strict_types=1);

namespace App\Entity;

use AnzuSystems\CoreDamBundle\Entity\Distribution;
use App\Repository\ArtemisDistributionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArtemisDistributionRepository::class)]
class ArtemisDistribution extends Distribution
{
}
