<?php

namespace PHPUnuhi\Commands\Core;

use PHPUnuhi\Bundles\Exchange\ExchangeFormat;
use PHPUnuhi\Configuration\ConfigurationLoader;
use PHPUnuhi\Services\Maths\PercentageCalculator;
use PHPUnuhi\Services\WordCounter\WordCounter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StatusCommand extends Command
{

    use \PHPUnuhi\Traits\CommandTrait;

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Show the status and statistics of your translations')
            ->addOption('configuration', null, InputOption::VALUE_REQUIRED, '', '')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'R', ExchangeFormat::CSV);

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('PHPUnuhi Status');
        $this->showHeader();

        # -----------------------------------------------------------------

        $configFile = $this->getConfigFile($input);

        # -----------------------------------------------------------------

        $configLoader = new ConfigurationLoader();
        $config = $configLoader->load($configFile);

        $calculator = new PercentageCalculator();
        $wordCounter = new WordCounter();

        $totalTranslations = 0;
        $totalValidTranslations = 0;
        $totalWords = 0;

        foreach ($config->getTranslationSets() as $set) {

            $io->section('Translation Set: ' . $set->getName());

            $countMaxLocaleIDs = count($set->getAllTranslationIDs());
            $countSetIDs = $countMaxLocaleIDs * count($set->getLocales());

            $totalTranslations += $countSetIDs;

            $countSetValid = 0;
            $countWords = 0;

            foreach ($set->getLocales() as $locale) {
                $countSetValid += count($locale->getValidTranslations());

                foreach ($locale->getValidTranslations() as $translation) {
                    $countWords += $wordCounter->getWordCount($translation->getValue());
                }
            }

            $totalValidTranslations += $countSetValid;
            $totalWords += $countWords;

            $percent = $calculator->getRoundedPercentage($countSetValid, $countSetIDs);

            $io->writeln("Coverage: " . $percent . '% (' . $countSetValid . '/' . $countSetIDs . ')');

            foreach ($set->getLocales() as $locale) {
                $countLocaleIDs = count($locale->getTranslationIDs());
                $countLocaleValid = count($locale->getValidTranslations());

                $percent = $calculator->getRoundedPercentage($countLocaleValid, $countLocaleIDs);

                $io->writeln("   [" . $locale->getName() . '] Coverage: ' . $percent . '% (' . $countLocaleValid . '/' . $countLocaleIDs . ')');
            }


            $io->writeln('');
            $io->writeln("Words: " . $countWords);

            foreach ($set->getLocales() as $locale) {

                $tmpCountWords = 0;
                foreach ($locale->getValidTranslations() as $translation) {
                    $tmpCountWords += $wordCounter->getWordCount($translation->getValue());
                }

                $io->writeln("   [" . $locale->getName() . '] Words: ' . $tmpCountWords);
            }
        }

        $io->section('Total Sets [' . count($config->getTranslationSets()) . ']');

        $percent = $calculator->getRoundedPercentage($totalValidTranslations, $totalTranslations);

        $io->writeln('   Coverage: ' . $percent . '% (' . $totalValidTranslations . '/' . $totalTranslations . ')');
        $io->writeln('   Words: ' . $totalWords);

        exit(0);
    }

}