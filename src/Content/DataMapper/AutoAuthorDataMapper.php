<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Content\DataMapper;

use Manuxi\SuluArchiveBundle\Entity\ArchiveDimensionContent;
use Sulu\Bundle\ContactBundle\Entity\ContactInterface;
use Sulu\Component\Security\Authentication\UserInterface;
use Sulu\Content\Application\ContentDataMapper\DataMapper\DataMapperInterface;
use Sulu\Content\Domain\Model\DimensionContentInterface;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Automatically sets the author to the current user if not provided.
 */
class AutoAuthorDataMapper implements DataMapperInterface
{
    public function __construct(
        private readonly ?Security $security,
    ) {
    }

    public function map(
        DimensionContentInterface $unlocalizedDimensionContent,
        DimensionContentInterface $localizedDimensionContent,
        array $data,
    ): void {
        if (!$localizedDimensionContent instanceof ArchiveDimensionContent) {
            return;
        }

        // Skip if author is explicitly provided
        if (\array_key_exists('author', $data) && null !== $data['author']) {
            return;
        }

        // Skip if author is already set
        if (null !== $localizedDimensionContent->getAuthor()) {
            return;
        }

        $contact = $this->getCurrentUserContact();
        if (null === $contact) {
            return;
        }

        $localizedDimensionContent->setAuthor($contact);

        // Set authored date if not set
        if (null === $localizedDimensionContent->getAuthored()) {
            $localizedDimensionContent->setAuthored(new \DateTimeImmutable());
        }
    }

    private function getCurrentUserContact(): ?ContactInterface
    {
        if (null === $this->security) {
            return null;
        }

        $user = $this->security->getUser();

        if (!$user instanceof UserInterface) {
            return null;
        }

        return $user->getContact();
    }
}
