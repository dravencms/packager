<?php declare(strict_types = 1);

namespace Dravencms\Packager\Console;

use Dravencms\Packager\Packager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class SyncCommand extends Command
{
    protected static $defaultName = 'packager:sync';
    protected static $defaultDescription = 'Sync dravencms module';

    /** @var Packager */
    private $packager;

    public function __construct(Packager $packager)
    {
        parent::__construct();

        $this->packager = $packager;
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            foreach ($this->packager->installAvailable() AS $package) {
                $output->writeln(sprintf('<info>Installing: %s</info>', $package->getName()));
            }
            
            foreach ($this->packager->uninstallAbsent() AS $package) {
                $output->writeln(sprintf('<info>Uninstalling: : %s</info>', $package->getName()));
            }
            
            $output->writeLn('Packages synced successfully');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}