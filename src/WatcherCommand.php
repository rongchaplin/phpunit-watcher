<?php

namespace T73Biz\PhpUnitWatcher;

use T73Biz\PhpUnitWatcher\Exceptions\InvalidConfigfile;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Yaml\Yaml;

class WatcherCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('watch')
            ->setDescription('Rerun PHPUnit tests when source code changes.')
            ->addArgument('phpunit-options', InputArgument::OPTIONAL, 'Options passed to phpunit');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     * @throws \T73Biz\PhpUnitWatcher\Exceptions\InvalidConfigfile
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $options = $this->determineOptions($input);

        [$watcher, $options] = WatcherFactory::create($options);

        $this->displayOptions($options, $input, $output);

        $watcher->startWatching();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return array
     * @throws \T73Biz\PhpUnitWatcher\Exceptions\InvalidConfigfile
     */
    protected function determineOptions(InputInterface $input): array
    {
        $options = $this->getOptionsFromConfigFile();

        $commandLineArguments = trim($input->getArgument('phpunit-options') ?? '', "'");

        if (! empty($commandLineArguments)) {
            $options['phpunit']['arguments'] = $commandLineArguments;
        }

        if (OS::isOnWindows()) {
            $options['hideManual'] = true;
        }

        return $options;
    }

    /**
     * @return array
     * @throws \T73Biz\PhpUnitWatcher\Exceptions\InvalidConfigfile
     */
    protected function getOptionsFromConfigFile(): array
    {
        $configFilePath = $this->getConfigFileLocation();

        if (! $configFilePath) {
            return [];
        }

        if (! file_exists($configFilePath)) {
            return [];
        }

        $options = Yaml::parse(file_get_contents($configFilePath));

        if (is_null($options)) {
            throw InvalidConfigfile::invalidContents($configFilePath);
        }

        $options['configFilePath'] = $configFilePath;

        return $options;
    }

    /**
     * @return string|void
     */
    protected function getConfigFileLocation()
    {
        $configNames = [
            '.phpunit-watcher.yml',
            'phpunit-watcher.yml',
            'phpunit-watcher.yml.dist',
        ];

        $configDirectory = getcwd();

        while (is_dir($configDirectory)) {
            foreach ($configNames as $configName) {
                $configFullPath = $configDirectory.DIRECTORY_SEPARATOR.$configName;

                if (file_exists($configFullPath)) {
                    return $configFullPath;
                }
            }

            $parentDirectory = dirname($configDirectory);

            // We do a direct comparison here since there's a difference between
            // the root directories on windows / *nix systems which does not
            // let us compare it against the DIRECTORY_SEPARATOR directly
            if ($parentDirectory === $configDirectory) {
                return;
            }

            $configDirectory = $parentDirectory;
        }
    }

    /**
     * @param array $options
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return void
     */
    protected function displayOptions(array $options, InputInterface $input, OutputInterface $output): void
    {
        $output = new SymfonyStyle($input, $output);

        $output->title('PHPUnit Watcher');

        $output->text("PHPUnit Watcher {$this->getApplication()->getVersion()} by T73Biz and contributors.");
        $output->newLine();

        if (isset($options['configFilePath'])) {
            $output->text("Using options from configfile at `{$options['configFilePath']}`");
        } else {
            $output->text('No config file detected. Using default options.');
        }
        $output->newLine();

        $fileMask = is_array($options['watch']['fileMask']) ? implode(',', $options['watch']['fileMask']) : $options['watch']['fileMask'];
        $output->text("Tests will be rerun when {$fileMask} files are modified in");

        $output->listing($options['watch']['directories']);

        $output->newLine();
    }
}
