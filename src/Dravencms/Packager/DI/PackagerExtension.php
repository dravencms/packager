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
    /** @var string */
    private $appDir;

    /** @var string */
    private $vendorDir;

    /** @var string */
    private $wwwDir;

    /** @var string */
    private $configDir;

    /**
     * PackagerExtension constructor.
     * @param string|null $appDir
     * @param string|null $vendorDir
     * @param string|null $wwwDir
     */
    public function __construct(string $appDir = null, string $vendorDir = null, string $wwwDir = null)
    {
        $this->appDir = $appDir;
        $this->vendorDir = $vendorDir;
        $this->wwwDir = $wwwDir;
        $this->configDir = $appDir.'/config';
    }

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('packager'))
            ->setFactory(Packager::class, [$this->configDir, $this->vendorDir, $this->appDir, $this->wwwDir]);

        $builder->addDefinition($this->prefix('composer'))
            ->setFactory(Composer::class, [$this->vendorDir]);

        $builder->addDefinition($this->prefix('script'))
            ->setFactory(Script::class, [$this->configDir]);

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
