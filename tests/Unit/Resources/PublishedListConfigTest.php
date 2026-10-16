<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;

class PublishedListConfigTest extends TestCase
{
    public function testPublishedListJoinsLiveRowWithoutWorkflowPlace(): void
    {
        // Sulu 3 leaves workflowPlace NULL on live rows, so the join must not depend on it.
        $xml = \simplexml_load_file(__DIR__ . '/../../../src/Resources/config/lists/archives_published.xml');
        $this->assertNotFalse($xml);

        $xml->registerXPathNamespace('l', 'http://schemas.sulu.io/list-builder/list');
        $conditions = $xml->xpath('//l:joins[@name="dimensionContent"]/l:join/l:condition');
        $this->assertNotEmpty($conditions);

        $condition = \preg_replace('/\s+/', ' ', (string) $conditions[0]);
        $this->assertStringContainsString("dimensionContent.stage = 'live'", $condition);
        $this->assertStringNotContainsString('workflowPlace', $condition);
    }
}
