<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Automation;

use Doctrine\ORM\EntityManagerInterface;
use Manuxi\SuluArchiveBundle\Domain\Event\ArchiveUnpublishedEvent;
use Manuxi\SuluArchiveBundle\Entity\Archive;
use Manuxi\SuluSharedToolsBundle\Search\Event\PreUpdatedEvent as SearchPreUpdatedEvent;
use Manuxi\SuluSharedToolsBundle\Search\Event\UpdatedEvent as SearchUpdatedEvent;
use Sulu\Bundle\ActivityBundle\Application\Collector\DomainEventCollectorInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\AutomationTaskHandlerInterface;
use Sulu\Bundle\AutomationBundle\TaskHandler\TaskHandlerConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ArchiveUnpublishTaskHandler implements AutomationTaskHandlerInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslatorInterface $translator,
        private readonly DomainEventCollectorInterface $domainEventCollector,
        private readonly EventDispatcherInterface $dispatcher
    ) {}

    public function handle($workload): void
    {
        if (!\is_array($workload)) {
            return;
        }
        $class = $workload['class'];
        $repository = $this->entityManager->getRepository($class);
        $entity = $repository->findById((int)$workload['id'], $workload['locale']);
        if ($entity === null) {
            return;
        }
        $this->dispatcher->dispatch(new SearchPreUpdatedEvent($entity));
        $entity->setPublished(false);

        $this->domainEventCollector->collect(
            new ArchiveUnpublishedEvent($entity, $workload)
        );

        $repository->save($entity);
        $this->dispatcher->dispatch(new SearchUpdatedEvent($entity));
    }

    public function configureOptionsResolver(OptionsResolver $optionsResolver): OptionsResolver
    {
        return $optionsResolver->setRequired(['id', 'locale'])
            ->setAllowedTypes('id', 'string')
            ->setAllowedTypes('locale', 'string');
    }

    public function supports(string $entityClass): bool
    {
        return $entityClass === Archive::class || \is_subclass_of($entityClass, Archive::class);
    }

    public function getConfiguration(): TaskHandlerConfiguration
    {
        return TaskHandlerConfiguration::create($this->translator->trans("sulu_archive.unpublish", [], 'admin'));
    }
}
