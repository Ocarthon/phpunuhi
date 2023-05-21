<?php

namespace PHPUnuhi\Services\Coverage\Models;

use PHPUnuhi\Services\Coverage\Traits\CoverageDataTrait;

class CoverageSet
{

    use CoverageDataTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var CoverageLocale[]
     */
    private $localeCoverages;


    /**
     * @param string $name
     * @param CoverageLocale[] $localeCoverages
     */
    public function __construct(string $name, array $localeCoverages)
    {
        $this->name = $name;
        $this->localeCoverages = $localeCoverages;

        $this->calculate();
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return CoverageLocale[]
     */
    public function getLocaleCoverages(): array
    {
        return $this->localeCoverages;
    }


    /**
     * @return void
     */
    private function calculate(): void
    {
        $this->countAll = 0;
        $this->countTranslated = 0;
        $this->countWords = 0;

        foreach ($this->localeCoverages as $coverage) {

            $this->countTranslated += $coverage->getCountTranslated();
            $this->countAll += $coverage->getCountAll();
            $this->countWords += $coverage->getWordCount();
        }
    }
}
