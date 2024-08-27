<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity\Interfaces;

use DateTime;

interface AuthoredInterface
{
    public function getAuthored(): ?DateTime;
    public function setAuthored(?DateTime $authored);
}
