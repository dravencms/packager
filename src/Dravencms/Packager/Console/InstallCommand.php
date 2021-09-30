<?php declare(strict_types = 1);

namespace Dravencms\Packager\Console;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class InstallCommand extends BaseCommand
{
    protected static $defaultName = 'packager:install';
    protected static $defaultDescription = 'Installs dravencms package';

    protected function configure(): void
    {
        $this->addArgument('package', InputArgument::REQUIRED, 'Package name');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $package = $this->packager->createPackageInstance($input->getArgument('package'));

            if ($this->packager->isInstalled($package)) {
                $output->writeln(sprintf('<info>Package %s is already installed, reinstalling...</info>', $package->getName()));
            }

            if ($this->packager->isConfigUserModified($package)) {
                $this->configAction($input, $output, $package);
            } else {
                $this->packager->generatePackageConfig($package);
            }

            $this->packager->install($package);

            $output->writeLn('Package installed successfully');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}