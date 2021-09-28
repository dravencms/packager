<?php declare(strict_types = 1);

namespace Dravencms\Packager\DI;


use Dravencms\Packager\Composer;
use Dravencms\Packager\Packager;
use Dravencms\Packager\Script;
use Nette\DI\CompilerExtension;

/**
 * Class Packagerxtension
 * @package Dravencms\Packager\DI
 */
class PackagerExtension extends CompilerExtension
{
    public $defaults = [
        'appDir' => '%appDir%',
        'wwwDir' => '%wwwDir%',
        'tempDir' => '%tempDir%',
        'configDir' => '%appDir%/config',
        'vendorDir' => '%appDir%/../vendor'
    ];

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        $this->setConfig($this->defaults);
        $config = $this->getConfig();

        $builder->addDefinition($this->prefix('packager'))
            ->setFactory(Packager::class, [$config['configDir'], $config['vendorDir'], $config['appDir'], $config['wwwDir'], $config['tempDir']]);

        $builder->addDefinition($this->prefix('composer'))
            ->setFactory(Composer::class, [$config['vendorDir']]);

        $builder->addDefinition($this->prefix('script'))
            ->setFactory(Script::class, [$config['configDir']]);

        $this->loadComponents();
        $this->loadModels();
        $this->loadConsole();
    }


    protected function loadComponents(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/components.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('components.' . $i))
                ->setAutowired(false);
            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadModels(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->loadFromFile(__DIR__ . '/models.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('models.' . $i))
                ->setAutowired(false);
            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }

    protected function loadConsole(): void
    {
        $builder = $this->getContainerBuilder();

        foreach ($this->loadFromFile(__DIR__ . '/console.neon') as $i => $command) {
            $cli = $builder->addDefinition($this->prefix('cli.' . $i))
                ->setAutowired(false);

            if (is_string($command)) {
                $cli->setFactory($command);
            } else {
                throw new \InvalidArgumentException;
            }
        }
    }
}
