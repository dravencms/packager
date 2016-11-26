<?php

namespace Dravencms\Packager\Console;

use Dravencms\Packager\Packager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class UninstallCommand extends Command
{
    /** @var Packager */
    private $packager;

    public function __construct(Packager $packager)
    {
        parent::__construct();

        $this->packager = $packager;
    }

    protected function configure()
    {
        $this->setName('packager:uninstall')
            ->addArgument('package', InputArgument::REQUIRED, 'Package name')
            ->addOption('purge', 'p', InputOption::VALUE_NONE, 'Purge configuration')
            ->setDescription('Uninstalls dravencms module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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