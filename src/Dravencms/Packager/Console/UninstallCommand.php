<?php declare(strict_types = 1);

namespace Dravencms\Packager\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class UninstallCommand extends BaseCommand
{
    protected static $defaultName = 'packager:uninstall';
    protected static $defaultDescription = 'Uninstalls dravencms package';


    protected function configure(): void
    {
        $this->addArgument('package', InputArgument::REQUIRED, 'Package name')
            ->addOption('purge', 'p', InputOption::VALUE_NONE, 'Purge configuration');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $package = $this->packager->createPackageInstance($input->getArgument('package'));

            $purge = $input->getOption('purge');

            if (!$this->packager->isInstalled($package)) {
                $output->writeln(sprintf('<info>Package %s is not installed, exiting...</info>', $package->getName()));
            }

            $this->packager->uninstall($package, $purge);

            $output->writeLn('Package uninstalled successfully');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}