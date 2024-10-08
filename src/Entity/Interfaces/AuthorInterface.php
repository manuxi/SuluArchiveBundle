<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity\Interfaces;

use Sulu\Bundle\ContactBundle\Entity\ContactInterface;

interface AuthorInterface
{
    public function getAuthor(): ?ContactInterface;
    public function setAuthor(?ContactInterface $author);
}
