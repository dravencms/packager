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

class ListCommand extends Command
{
    protected function configure()
    {
        $this->setName('packager:list')
            ->setDescription('List dravencms module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeLn('Module listed successfully');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}