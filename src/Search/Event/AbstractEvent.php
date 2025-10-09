<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Search\Event;

use Manuxi\SuluArchiveBundle\Entity\Archive;
use Symfony\Contracts\EventDispatcher\Event as SymfonyEvent;

abstract class AbstractEvent extends SymfonyEvent
{
    public function __construct(public Archive $entity) {}

    public function getEntity(): Archive
    {
        return $this->entity;
    }
}