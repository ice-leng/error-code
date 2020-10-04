<?php

namespace Lengbin\ErrorCode\Command;

use Lengbin\Common\Component\BaseObject;
use Lengbin\Helper\Util\FileHelper;
use Lengbin\Helper\Util\TemplateHelper;
use Lengbin\Helper\YiiSoft\Arrays\ArrayHelper;
use Lengbin\Helper\YiiSoft\StringHelper;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Type\DirectoriesSourceLocator;

class Merge extends BaseObject
{
    /**
     * @var array
     */
    protected $prefix = [];

    /**
     * @var array
     */
    protected $constantValue = [];

    /**
     * paths
     * @var array
     */
    private $path = [];

    /**
     * class name
     * @var string
     */
    private $classname;

    /**
     * class namespace
     * @var string
     */
    private $classNamespace;

    /**
     *  output
     * @var string
     */
    private $output;

    /**
     * @return array
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @param array $path
     *
     * @return Merge
     */
    public function setPath(array $path): Merge
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getClassname(): string
    {
        return $this->classname;
    }

    /**
     * @param string $classname
     *
     * @return Merge
     */
    public function setClassname(string $classname): Merge
    {
        $this->classname = $classname;
        return $this;
    }

    /**
     * @return string
     */
    public function getClassNamespace(): string
    {
        return $this->classNamespace;
    }

    /**
     * @param string $classNamespace
     *
     * @return Merge
     */
    public function setClassNamespace(string $classNamespace): Merge
    {
        $this->classNamespace = $classNamespace;
        return $this;
    }

    /**
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * @param string $output
     *
     * @return Merge
     */
    public function setOutput(string $output): Merge
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @param string $path
     * @param string $filePath
     *
     * @return string
     */
    protected function getPrefix(string $path, string $filePath): string
    {
        if (ArrayHelper::isValidValue($this->prefix, $filePath)) {
            return $this->prefix[$filePath];
        }
        $prefix = 'ERROR';
        if ($path === $filePath) {
            $this->prefix[$filePath] = $prefix;
            return $prefix;
        }
        $substr = StringHelper::substr($filePath, StringHelper::strlen($path));
        $names = StringHelper::explode($substr, '/', true, true);
        array_unshift($names, $prefix);
        $names = array_map(function ($name) {
            return StringHelper::strtoupper($name);
        }, $names);
        $string = implode('_', $names);
        $this->prefix[$filePath] = $string;
        return $string;
    }

    /**
     * @return string
     */
    protected function getStub(): string
    {
        return __DIR__ . '/stubs/error-code.stub';
    }

    /**
     * @return string
     */
    protected function generatePath(): string
    {
        return $this->getOutput() . '/' . $this->getClassname() . '.php';
    }

    /**
     * @param array $data
     *
     * @return bool|int
     */
    protected function buildClass(array $data)
    {
        $template = new TemplateHelper(file_get_contents($this->getStub()), "%", "%");
        $template->place('NAMESPACE', $this->getClassNamespace())->place('CLASSNAME', $this->getClassname())->place('CONSTANT', implode(PHP_EOL, $data));
        return FileHelper::putFile($this->generatePath(), $template->produce());
    }

    public function generate()
    {
        $data = [];
        $paths = $this->getPath();

        $astLocator = (new BetterReflection())->astLocator();
        foreach ($paths as $path) {
            $directoriesSourceLocator = new DirectoriesSourceLocator([$path], $astLocator);
            $reflector = new ClassReflector($directoriesSourceLocator);
            $classes = $reflector->getAllClasses();
            foreach ($classes as $class) {
                $prefix = $this->getPrefix($path, StringHelper::dirname($class->getFileName()));
                $classInfo = (new BetterReflection())->classReflector()->reflect($class->getName());
                $constants = $classInfo->getReflectionConstants();
                foreach ($constants as $constant) {
                    $name = implode('_', [$prefix, StringHelper::strtoupper(StringHelper::basename($class->getFileName(), '.php')), $constant->getName()]);
                    $data[] = implode(PHP_EOL . "   ", [
                        "    " . implode(PHP_EOL . "    ", explode(PHP_EOL, $constant->getDocComment())),
                        "const {$name} = '{$constant->getValue()}';",
                        '',
                    ]);
                    $const = "{$class->getName()}::{$constant->getName()}";
                    if (ArrayHelper::isValidValue($this->constantValue, $constant->getValue())) {
                        throw new \RuntimeException("Constant {$this->constantValue[$constant->getValue()]} and {$const} value repeat");
                    }
                    $this->constantValue[$constant->getValue()] = $const;
                }
            }
        }

        return $this->buildClass($data);
    }
}
