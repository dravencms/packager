<?php
/**
 * Created by PhpStorm.
 * User: sadam
 * Date: 9/30/21
 * Time: 11:08 PM
 */

namespace Dravencms\Packager\Console;

use Dravencms\Packager\IPackage;
use Dravencms\Packager\Packager;
use Symfony\Component\Console\Command\Command;
use Nette\Neon\Neon;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BaseCommand extends Command
{
    const CONFIG_ACTION_KEEP = 'k';
    const CONFIG_ACTION_DIFF = 'd';
    const CONFIG_ACTION_QUIT = 'q';
    const CONFIG_ACTION_OVERWRITE = 'o';

    /** @var Packager */
    public $packager;

    /**
     * BaseCommand constructor.
     * @param Packager $packager
     */
    public function __construct(Packager $packager)
    {
        $this->packager = $packager;
        parent::__construct();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param IPackage $package
     */
    public function configAction(InputInterface $input, OutputInterface $output, IPackage $package): void
    {
        $helper = $this->getHelper('question');
        $question = new Question(sprintf('Configuration file %s is user modified, what now ? [k=keep(default) / d=diff / q=quit / o=overwrite]', $this->packager->getConfigPath($package)),
            self::CONFIG_ACTION_KEEP);
        $action = $helper->ask($input, $output, $question);

        switch ($action) {
            case self::CONFIG_ACTION_DIFF:
                $differ = new Differ;

                $diff = $differ->diff($this->packager->getPackageInstalledConfiguration($package), $this->packager->getPackageInstallConfiguration($package));

                $output->writeln($diff);
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
                return;
                break;
        }
    }

}