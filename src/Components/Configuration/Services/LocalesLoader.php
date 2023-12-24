<?php

namespace PHPUnuhi\Configuration\Services;

use PHPUnuhi\Exceptions\ConfigurationException;
use PHPUnuhi\Models\Translation\Locale;
use PHPUnuhi\Traits\XmlTrait;
use SimpleXMLElement;

class LocalesLoader
{
    use XmlTrait;

    /**
     * @param SimpleXMLElement $rootLocales
     * @param string $configFilename
     * @throws ConfigurationException
     * @return array<mixed>
     */
    public function loadLocales(SimpleXMLElement $rootLocales, string $configFilename): array
    {
        $foundLocales = [];

        # load optional <locale basePath=xy">
        $basePath = $this->getAttribute('basePath', $rootLocales);

        foreach ($rootLocales->children() as $nodeLocale) {
            $nodeType = $nodeLocale->getName();
            $innerValue = trim((string)$nodeLocale[0]);

            if ($nodeType !== 'locale') {
                continue;
            }

            $localeName = (string)$nodeLocale['name'];
            $localeFile = '';
            $iniSection = (string)$nodeLocale['iniSection'];

            if ($innerValue !== '') {
                # replace our locale-name placeholders
                $innerValue = str_replace('%locale%', $localeName, $innerValue);
                $innerValue = str_replace('%locale_uc%', strtoupper($localeName), $innerValue);
                $innerValue = str_replace('%locale_lc%', strtolower($localeName), $innerValue);

                # if we have a basePath, we also need to replace any values
                if (trim($basePath->getValue()) !== '') {
                    $innerValue = str_replace('%base_path%', $basePath->getValue(), $innerValue);
                }

                # for now treat inner value as file
                $configuredFileName = dirname($configFilename) . '/' . $innerValue;
                $localeFile = file_exists($configuredFileName) ? (string)realpath($configuredFileName) : $configuredFileName;

                # clear duplicate slashes that exist somehow
                $localeFile = str_replace('//', '/', $localeFile);
                # replace duplicate occurrences of ./
                $localeFile = str_replace('././', './', $localeFile);
            }

            $foundLocales[] = new Locale($localeName, $localeFile, $iniSection);
        }

        return $foundLocales;
    }
}
