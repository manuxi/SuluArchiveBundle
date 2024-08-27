<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Entity\Models;

use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Manuxi\SuluArchiveBundle\Entity\ArchiveExcerpt;
use Manuxi\SuluArchiveBundle\Entity\Interfaces\ArchiveExcerptModelInterface;
use Manuxi\SuluArchiveBundle\Entity\Traits\ArrayPropertyTrait;
use Manuxi\SuluArchiveBundle\Repository\ArchiveExcerptRepository;
use Sulu\Bundle\CategoryBundle\Category\CategoryManagerInterface;
use Sulu\Bundle\MediaBundle\Entity\MediaRepositoryInterface;
use Sulu\Bundle\TagBundle\Tag\TagManagerInterface;
use Sulu\Component\Rest\Exception\EntityNotFoundException;
use Symfony\Component\HttpFoundation\Request;

class ArchiveExcerptModel implements ArchiveExcerptModelInterface
{
    use ArrayPropertyTrait;

    private ArchiveExcerptRepository $archiveExcerptRepository;
    private CategoryManagerInterface $categoryManager;
    private TagManagerInterface $tagManager;
    private MediaRepositoryInterface $mediaRepository;

    public function __construct(
        ArchiveExcerptRepository $archiveExcerptRepository,
        CategoryManagerInterface $categoryManager,
        TagManagerInterface $tagManager,
        MediaRepositoryInterface $mediaRepository
    ) {
        $this->archiveExcerptRepository = $archiveExcerptRepository;
        $this->categoryManager = $categoryManager;
        $this->tagManager = $tagManager;
        $this->mediaRepository = $mediaRepository;
    }

    /**
     * @param ArchiveExcerpt $archiveExcerpt
     * @param Request $request
     * @return ArchiveExcerpt
     * @throws EntityNotFoundException
     */
    public function updateArchiveExcerpt(ArchiveExcerpt $archiveExcerpt, Request $request): ArchiveExcerpt
    {
        $archiveExcerpt = $this->mapDataToArchiveExcerpt($archiveExcerpt, $request->request->all()['ext']['excerpt']);
        return $this->archiveExcerptRepository->save($archiveExcerpt);
    }

    /**
     * @param ArchiveExcerpt $archiveExcerpt
     * @param array $data
     * @return ArchiveExcerpt
     * @throws EntityNotFoundException
     */
    private function mapDataToArchiveExcerpt(ArchiveExcerpt $archiveExcerpt, array $data): ArchiveExcerpt
    {
        $locale = $this->getProperty($data, 'locale');
        if ($locale) {
            $archiveExcerpt->setLocale($locale);
        }

        $title = $this->getProperty($data, 'title');
        if ($title) {
            $archiveExcerpt->setTitle($title);
        }

        $more = $this->getProperty($data, 'more');
        if ($more) {
            $archiveExcerpt->setMore($more);
        }

        $description = $this->getProperty($data, 'description');
        if ($description) {
            $archiveExcerpt->setDescription($description);
        }

        $categoryIds = $this->getProperty($data, 'categories');
        if ($categoryIds && is_array($categoryIds)) {
            $archiveExcerpt->removeCategories();
            $categories = $this->categoryManager->findByIds($categoryIds);
            foreach($categories as $category) {
                $archiveExcerpt->addCategory($category);
            }
        }

        $tags = $this->getProperty($data, 'tags');
        if ($tags && is_array($tags)) {
            $archiveExcerpt->removeTags();
            foreach($tags as $tagName) {
                $archiveExcerpt->addTag($this->tagManager->findOrCreateByName($tagName));
            }
        }

        $iconIds = $this->getPropertyMulti($data, ['icon', 'ids']);
        if ($iconIds && is_array($iconIds)) {
            $archiveExcerpt->removeIcons();
            foreach($iconIds as $iconId) {
                $icon = $this->mediaRepository->findMediaById((int)$iconId);
                if (!$icon) {
                    throw new EntityNotFoundException($this->mediaRepository->getClassName(), $iconId);
                }
                $archiveExcerpt->addIcon($icon);
            }
        }

        $imageIds = $this->getPropertyMulti($data, ['images', 'ids']);
        if ($imageIds && is_array($imageIds)) {
            $archiveExcerpt->removeImages();
            foreach($imageIds as $imageId) {
                $image = $this->mediaRepository->findMediaById((int)$imageId);
                if (!$image) {
                    throw new EntityNotFoundException($this->mediaRepository->getClassName(), $imageId);
                }
                $archiveExcerpt->addImage($image);
            }
        }

        return $archiveExcerpt;
    }
}
