<?php

namespace PHPUnuhi\Bundles\Translator\Fake;

use Exception;
use PHPUnuhi\Bundles\Translator\TranslatorInterface;
use PHPUnuhi\Models\Command\CommandOption;
use PHPUnuhi\Services\Placeholder\Placeholder;

class FakeTranslator implements TranslatorInterface
{

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'fake';
    }

    /**
     * @return CommandOption[]
     */
    public function getOptions(): array
    {
        return [];
    }

    /**
     * @param array<mixed> $options
     * @return void
     */
    public function setOptionValues(array $options): void
    {
    }

    /**
     * @param string $text
     * @param string $sourceLocale
     * @param string $targetLocale
     * @param Placeholder[] $foundPlaceholders
     * @return string
     * @throws Exception
     */
    public function translate(string $text, string $sourceLocale, string $targetLocale, array $foundPlaceholders): string
    {
        return $targetLocale . '-' . $text;
    }

}
