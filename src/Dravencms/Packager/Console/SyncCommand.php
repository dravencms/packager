<?php declare(strict_types = 1);

namespace Dravencms\Packager\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class SyncCommand extends BaseCommand
{
    protected static $defaultName = 'packager:sync';
    protected static $defaultDescription = 'Sync dravencms module';

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            foreach ($this->packager->getAvailableForSync() AS $package) {
                if ($this->packager->isConfigUserModified($package)) {
                    $this->configAction($input, $output, $package);
                } else {
                    $this->packager->generatePackageConfig($package);
                }

                if (!$this->packager->isInstalled($package)){
                    $this->packager->install($package);
                    $output->writeln(sprintf('<info>Installing: %s</info>', $package->getName()));
                }
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