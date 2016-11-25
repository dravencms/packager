<?php

namespace Dravencms\Packager\Console;

use App\Model\User\Repository\AclResourceRepository;
use Kdyby\Doctrine\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class SyncCommand extends Command
{
    protected function configure()
    {
        $this->setName('packager:sync')
            ->setDescription('Sync dravencms module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {

            $output->writeLn('Module synced successfully');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}