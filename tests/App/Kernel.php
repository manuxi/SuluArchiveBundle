<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\App;

use Manuxi\SuluArchiveBundle\SuluArchiveBundle;
use Sulu\Bundle\TestBundle\Kernel\SuluTestKernel;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class Kernel extends SuluTestKernel
{
    /**
     * @return BundleInterface[]
     */
    public function registerBundles(): array
    {
        /** @var BundleInterface[] $bundles */
        $bundles = parent::registerBundles();
        $bundles[] = new SuluArchiveBundle();

        return $bundles;
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }
}
