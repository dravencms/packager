<?php

namespace Dravencms\Packager\Console;

use App\Model\Admin\Entities\Menu;
use App\Model\Admin\Repository\MenuRepository;
use App\Model\User\Entities\AclOperation;
use App\Model\User\Entities\AclResource;
use Kdyby\Doctrine\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */

class InstallCommand extends Command
{
    protected function configure()
    {
        $this->setName('dravencms:faq:install')
            ->setDescription('Installs dravencms module');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var MenuRepository $adminMenuRepository */
        $adminMenuRepository = $this->getHelper('container')->getByType('App\Model\Admin\Repository\MenuRepository');

        /** @var EntityManager $entityManager */
        $entityManager = $this->getHelper('container')->getByType('Kdyby\Doctrine\EntityManager');

        try {

            $aclResource = new AclResource('faq', 'Faq');

            $entityManager->persist($aclResource);

            $aclOperation = new AclOperation($aclResource, 'edit', 'Allows editation of faq');
            $entityManager->persist($aclOperation);
            $aclOperation = new AclOperation($aclResource, 'delete', 'Allows deletion of faq');
            $entityManager->persist($aclOperation);

            $adminMenu = new Menu('Faq', ':Admin:Faq:Faq', 'fa-question-circle', $aclOperation);

            $foundRoot = $adminMenuRepository->getOneByName('Site items');

            if ($foundRoot)
            {
                $adminMenuRepository->getMenuRepository()->persistAsLastChildOf($adminMenu, $foundRoot);
            }
            else
            {
                $entityManager->persist($adminMenu);
            }

            $entityManager->flush();

            $output->writeLn('Module installed successfully');
            return 0; // zero return code means everything is ok

        } catch (\Exception $e) {
            $output->writeLn('<error>' . $e->getMessage() . '</error>');
            return 1; // non-zero return code means error
        }
    }
}