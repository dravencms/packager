<?php

namespace Dravencms\Packager\Console;

use Dravencms\Packager\IPackage;
use Dravencms\Packager\Packager;
use Nette\Neon\Neon;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Copyright (C) 2016 Adam Schubert <adam.schubert@sg1-game.net>.
 */
class InstallCommand extends Command
{
    const CONFIG_ACTION_KEEP = 'k';
    const CONFIG_ACTION_DIFF = 'd';
    const CONFIG_ACTION_QUIT = 'q';
    const CONFIG_ACTION_OVERWRITE = 'o';

    /** @var Packager */
    private $packager;

    public function __construct(Packager $packager)
    {
        parent::__construct();

        $this->packager = $packager;
    }

    protected function configure()
    {
        $this->setName('packager:install')
            ->addArgument('package', InputArgument::REQUIRED, 'Package name')
            ->setDescription('Installs dravencms package');
    }

    private function configAction(InputInterface $input, OutputInterface $output, IPackage $package)
    {
        $helper = $this->getHelper('question');
        $question = new Question(sprintf('Configuration file %s is user modified, what now ? [k=keep(default) / d=diff / q=quit / o=overwrite]', $this->packager->getConfigPath($package)),
            self::CONFIG_ACTION_KEEP);
        $action = $helper->ask($input, $output, $question);

        switch ($action) {
            case self::CONFIG_ACTION_DIFF:
                $differ = new Differ;
                $installedConfig = Neon::decode(file_get_contents($this->packager->getConfigPath($package)));
                $output->writeln($differ->diff(Neon::encode($installedConfig, Neon::BLOCK), Neon::encode($package->getConfiguration(), Neon::BLOCK)));
                $this->configAction($input, $output, $package);
                break;
            case self::CONFIG_ACTION_KEEP:
                $output->writeln('<info>Keeping old configuration file</info>');
                //Do nothing
                break;
            case self::CONFIG_ACTION_OVERWRITE:
                $output->writeln('<info>Overwriting old configuration file</info>');
                $this->packager->generatePackageConfig($package);
                break;
            case self::CONFIG_ACTION_QUIT:
                return 0;
                break;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
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