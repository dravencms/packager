<?php

namespace Dravencms\Packager\DI;

use Kdyby\Console\DI\ConsoleExtension;
use Nette;
use Nette\DI\Compiler;
use Nette\DI\Configurator;
use Salamek\Cms\DI\CmsExtension;
/**
 * Class Packagerxtension
 * @package Dravencms\Packager\DI
 */
class PackagerExtension extends Nette\DI\CompilerExtension
{
    public $defaults = [
        'configDir' => '%appDir%/config',
        'vendorDir' => '%appDir%/../vendor'
    ];

    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();
        $config = $this->getConfig($this->defaults);

        $builder->addDefinition($this->prefix('packager'))
            ->setClass('Dravencms\Packager\Packager', [$config['configDir'], $config['vendorDir']]);

        $builder->addDefinition($this->prefix('composer'))
            ->setClass('Dravencms\Packager\Composer', [$config['vendorDir']]);

        $builder->addDefinition($this->prefix('script'))
            ->setClass('Dravencms\Packager\Script', [$config['configDir']]);

        $this->loadComponents();
        $this->loadModels();
        $this->loadConsole();
    }


    /**
     * @param Configurator $config
     * @param string $extensionName
     */
    public static function register(Configurator $config, $extensionName = 'packagerExtension')
    {
        $config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
            $compiler->addExtension($extensionName, new PackagerExtension());
        };
    }

    protected function loadComponents()
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/components.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('components.' . $i))
                ->setInject(FALSE); // lazy injects
            if (is_string($command)) {
                $cli->setImplement($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadModels()
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/models.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('models.' . $i))
                ->setInject(FALSE); // lazy injects
            if (is_string($command)) {
                $cli->setClass($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadConsole()
    {
        $builder = $this->getContainerBuilder();

        foreach ($this->loadFromFile(__DIR__ . '/console.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cli.' . $i))
                ->addTag(ConsoleExtension::TAG_COMMAND)
                ->setInject(FALSE); // lazy injects

            if (is_string($command)) {
                $cli->setClass($command);

            } else {
                throw new \InvalidArgumentException;
            }
        }
    }
}
