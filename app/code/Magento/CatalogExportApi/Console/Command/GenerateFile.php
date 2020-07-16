<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogExportApi\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Phrase;
use Magento\Framework\Xml\Parser;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Generate
 * @package Magento\CatalogExportApi\Console\Command\Generate
 */
class GenerateFile extends Command
{
    const FILE = 'file';

    const MODULE = 'module';

    /**
     * @var State
     */
    protected $appState;
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * GenerateFile constructor.
     * @param State $appState
     * @param Filesystem $filesystem
     * @param Reader $moduleDirReader
     * @param Parser $parser
     * @param File $fileDriver
     * @param null $name
     */
    public function __construct(
        State $appState,
        Filesystem $filesystem,
        Reader $moduleDirReader,
        Parser $parser,
        File $fileDriver,
        $name = null
    ) {
        parent::__construct($name);
        $this->appState = $appState;
        $this->filesystem = $filesystem;
        $this->moduleDirReader = $moduleDirReader;
        $this->parser = $parser;
        $this->fileDriver = $fileDriver;
    }

    protected function configure()
    {
        $this->setName('dto:generate');
        $this->setDescription('This will generate the provider class for a module or file.');
        $this->addOption(
            self::FILE,
            null,
            InputOption::VALUE_REQUIRED,
            __('File')
        );
        $this->addOption(
            self::MODULE,
            null,
            InputOption::VALUE_REQUIRED,
            __('Module')
        );
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     * @throws LocalizedException
     * @throws RuntimeException
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->appState->setAreaCode(Area::AREA_GLOBAL);
        if ($filePath = $input->getOption(self::FILE)) {
            if (!$this->fileDriver->isExists($filePath)) {
                throw new RuntimeException(new Phrase('FIle not found'));
            }
        } elseif ($moduleName = $input->getOption(self::MODULE)) {
            $filePath = $this->moduleDirReader->getModuleDir('etc', $moduleName) . '/et_schema.xml';
        } else {
            throw new RuntimeException(new Phrase('Parameter not set'));
        }
        $parsedArray = $this->parser->load($filePath)->xmlToArray();
        $generateArray = $this->buildArray($parsedArray, $this->resolveNameSpace($filePath));
        $this->createDirectory($this->resolveFileLocation($filePath));
        $this->generateFiles($generateArray, $this->resolveNameSpace($filePath), $this->resolveFileLocation($filePath));
        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param $filePath
     * @return string
     */
    protected function resolveNameSpace($filePath): string
    {
        $fileArray = explode('/', $filePath);
        return $fileArray[6] . '\\' . $fileArray[7] . '\Api\Data';
    }

    /**
     * @param $filePath
     * @return string
     */
    protected function resolveFileLocation($filePath): string
    {
        $filePath = chop($filePath, 'etc/et_schema.xml');
        return $filePath . '/Api/Data/';
    }

    /**
     * @param $parsedArray
     * @param $baseNameSpace
     * @return array
     */
    protected function buildArray($parsedArray, $baseNameSpace): array
    {
        $generateArray = [];
        foreach ($parsedArray['config']['_value']['record'] as $record) {
            foreach ($record['_value'] as $field) {
                if (isset($field['_attribute'])) {
                    $generateArray[$record['_attribute']['name']]['fields'][] = [
                        'name' => lcfirst(str_replace('_', '', ucwords($field['_attribute']['name'], '_'))),
                        'type' => $this->mapType($field['_attribute']['type'], $baseNameSpace)
                    ];
                }
            }
            foreach ($record['_value']['field'] as $field) {
                if (isset($field['_attribute'])) {
                    $generateArray[$record['_attribute']['name']]['fields'][] = [
                        'name' => lcfirst(str_replace('_', '', ucwords($field['_attribute']['name'], '_'))),
                        'type' => $this->mapType($field['_attribute']['type'], $baseNameSpace)
                    ];
                }
            }
        }
        return $generateArray;
    }

    /**
     * @param $type
     * @param $baseNameSpace
     * @return string
     */
    protected function mapType($type, $baseNameSpace): string
    {
        switch ($type) {
            case 'ID':
            case 'Int':
                $type = 'int';
                break;
            case 'String':
                $type = 'string';
                break;
            case 'Boolean':
                $type = 'bool';
                break;
            case 'Float':
                $type = 'float';
                break;
            default:
                $type = '\\' . $baseNameSpace . '\\' . $type . '[]|null';
        }

        return $type;
    }

    /**
     * @param $generateArray
     * @param $baseNameSpace
     * @param $baseFileLocation
     * @throws FileSystemException
     */
    protected function generateFiles($generateArray, $baseNameSpace, $baseFileLocation)
    {
        foreach ($generateArray as $key => $phpClass) {
            $file = new PhpFile();
            $file->addComment('Copyright © Magento, Inc. All rights reserved');
            $file->addComment('See COPYING.txt for license details.');
            $file->addComment('');
            $file->addComment('This file is auto-generated.');
            $file->setStrictTypes();
            $namespace = $file->addNamespace($baseNameSpace);
            $class = $namespace->addClass($key);
            $class->addComment($key . ' entity');
            $class->addComment('');
            $class->addComment('@SuppressWarnings(PHPMD.ExcessivePublicCount)');
            foreach ($phpClass['fields'] as $field) {
                $commentName = preg_replace('/(?<!\ )[A-Z]/', ' $0', $field['name']);
                $class->addProperty($field['name'])
                    ->setPrivate()
                    ->addComment('@var ' . $field['type']);
                $method = $class->addMethod('get' . ucfirst($field['name']));
                $method->addComment('Get ' . strtolower($commentName));
                $method->addComment('');
                $method->addComment('@return ' . $field['type']);
                if (strpos($field['type'], 'null') !== false) {
                    $method->setReturnType('array');
                } else {
                    $method->setReturnType($field['type']);
                }
                $method->setReturnNullable();
                $method->addBody('return $this->' . $field['name'] . ';');
                $method = $class->addMethod('set' . ucfirst($field['name']));
                $method->addComment('Set ' . strtolower($commentName));
                $method->addComment('');
                $method->addComment('@param ' . str_replace('|null', '', $field['type']) . ' $' . $field['name']);
                $method->addComment('@return void');
                if (strpos($field['type'], 'null') !== false) {
                    $method->addParameter($field['name'], null)->setType('array');
                } else {
                    $method->addParameter($field['name'])->setType($field['type']);
                }
                $method->setReturnType('void');
                $method->addBody('$this->' . $field['name'] . ' = $' . $field['name'] . ';');
            }
            $print = new PsrPrinter();
            $this->writeToFile($baseFileLocation . $key . '.php', $print->printFile($file));
        }
    }

    /**
     * @param $baseNameSpace
     * @throws FileSystemException
     */
    protected function createDirectory($baseNameSpace)
    {
        if (!$this->fileDriver->isExists($baseNameSpace)) {
            $this->fileDriver->createDirectory($baseNameSpace, 0755);
        }
    }

    /**
     * @param $fileLocation
     * @param $output
     * @throws FileSystemException
     */
    protected function writeToFile($fileLocation, $output)
    {
        $resource = $this->fileDriver->fileOpen($fileLocation, 'w');
        $this->fileDriver->fileWrite($resource, $output);
        $this->fileDriver->fileClose($resource);
    }
}
