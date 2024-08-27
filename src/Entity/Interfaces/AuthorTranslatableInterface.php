<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity\Interfaces;

use Sulu\Bundle\ContactBundle\Entity\ContactInterface;

interface AuthorTranslatableInterface
{
    public function getAuthor(): ?int;
    public function setAuthor(?ContactInterface $author);
}
