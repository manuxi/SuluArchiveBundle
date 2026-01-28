<?php

declare(strict_types=1);

namespace Manuxi\SuluArchiveBundle\Service;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Service for managing archive types with colors.
 * Types are loaded from bundle/app configuration (sulu_archive.types).
 */
class ArchiveTypeSelect
{
    private array $types;
    private string $defaultType;

    /**
     * @param array  $types       Archive types from configuration (injected via %sulu_archive.types%)
     * @param string $defaultType Default type key (injected via %sulu_archive.default_type%)
     */
    public function __construct(
        private TranslatorInterface $translator,
        array $types = [],
        string $defaultType = 'default',
    ) {
        $this->types = $types;
        $this->defaultType = $defaultType;
    }

    /**
     * Returns values in the format required by Sulu's single_select field type.
     */
    public function getValues(): array
    {
        $values = [];

        foreach ($this->types as $key => $config) {
            $values[] = [
                'name' => $key,
                'title' => $this->translator->trans($config['name'], [], 'admin'),
            ];
        }

        return $values;
    }

    /**
     * Get default value for new archives.
     */
    public function getDefaultValue(): string
    {
        return $this->defaultType;
    }

    /**
     * Get color for a specific type.
     * Falls back to default type color if type not found.
     */
    public function getColor(string $type): string
    {
        if (isset($this->types[$type]['color'])) {
            return $this->types[$type]['color'];
        }

        if (isset($this->types[$this->defaultType]['color'])) {
            return $this->types[$this->defaultType]['color'];
        }

        return '#cccccc';
    }

    /**
     * Get all configured types with their properties.
     */
    public function getTypes(): array
    {
        return $this->types;
    }

    /**
     * Get translated name for a type.
     * Falls back to default type if type not found.
     */
    public function getTypeName(string $type): string
    {
        if (!isset($this->types[$type])) {
            $type = $this->defaultType;
        }

        if (!isset($this->types[$type])) {
            return 'Default';
        }

        return $this->translator->trans($this->types[$type]['name'], [], 'admin');
    }

    /**
     * Check if a type exists in configuration.
     */
    public function hasType(string $type): bool
    {
        return isset($this->types[$type]);
    }
}
