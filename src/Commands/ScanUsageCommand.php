<?php

namespace PHPUnuhi\Commands;

use Exception;
use PHPUnuhi\Bundles\Storage\StorageFactory;
use PHPUnuhi\Bundles\Twig\ScannerFactory;
use PHPUnuhi\Configuration\ConfigurationLoader;
use PHPUnuhi\Exceptions\ConfigurationException;
use PHPUnuhi\Services\Loaders\Directory\DirectoryLoader;
use PHPUnuhi\Services\Loaders\File\FileLoader;
use PHPUnuhi\Services\Loaders\Xml\XmlLoader;
use PHPUnuhi\Traits\CommandTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ScanUsageCommand extends Command
{
    use CommandTrait;

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('scan:usage')
            ->setDescription('Scans the usage of translations in the provided files')
            ->addOption('configuration', null, InputOption::VALUE_REQUIRED, '', '')
            ->addOption('set', null, InputOption::VALUE_REQUIRED, '', '')
            ->addOption('dir', null, InputOption::VALUE_REQUIRED, '', '')
            ->addOption('scanner', null, InputOption::VALUE_REQUIRED, '', '');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws ConfigurationException
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('PHPUnuhi Scan Usage');
        $this->showHeader();

        # -----------------------------------------------------------------

        $configFile = $this->getConfigFile($input);
        $scannerName = $this->getConfigStringValue('scanner', $input);
        $directory = $this->getConfigStringValue('dir', $input);
        $setName = $this->getConfigStringValue('set', $input);


        if ($scannerName === '' || $scannerName === '0') {
            throw new Exception('No scanner provided');
        }

        if ($directory === '' || $directory === '0') {
            throw new Exception('No directory provided');
        }

        $directory = dirname($configFile) . '/' . $directory;

        $directoryLoader = new DirectoryLoader();
        $fileReader = new FileLoader();

        # -----------------------------------------------------------------

        $configLoader = new ConfigurationLoader(new XmlLoader());
        $config = $configLoader->load($configFile);

        $scanner = ScannerFactory::getInstance()->getScanner($scannerName);
        $io->writeln('Using scanner: ' . $scanner->getStorageName());


        $scannedFiles = $directoryLoader->getFiles($directory, 'twig');

        $fileContents = [];
        foreach ($scannedFiles as $file) {
            $fileContents[$file] = $fileReader->load($file);
        }


        if ($fileContents === []) {
            throw new Exception('No files found in directory: ' . $directory);
        }

        $errorCount = 0;

        foreach ($config->getTranslationSets() as $set) {
            if ($setName !== '' && $setName !== '0' && $setName !== $set->getName()) {
                continue;
            }

            $io->section('Translation-Set: ' . $set->getName());

            $storage = StorageFactory::getInstance()->getStorage($set);
            $storage->loadTranslationSet($set);

            foreach ($set->getAllTranslationIDs() as $translationID) {
                $foundInFiles = false;

                foreach ($fileContents as $content) {
                    $found = $scanner->findKey($translationID, $content);

                    if ($found) {
                        $foundInFiles = true;
                        # break, we have found that key
                        break;
                    }
                }

                if (!$foundInFiles) {
                    $io->writeln('    [x] Key "' . $translationID . '" not found in any file');
                    $errorCount++;
                }
            }
        }

        if ($errorCount > 0) {
            $io->error('Found ' . $errorCount . ' translation keys that do not seem to be used in any of the scanned files');
            $io->note('Please keep in mind, the keys have not been found in your scanned files, but might be used somewhere else, like in JS or PHP files. Do not delete them without further investigation!');
            return 1;
        }

        $io->success('All translation keys seem to be used in the scanned files');
        return 0;
    }
}
