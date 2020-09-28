<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Grpc\Console\Command;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Module\ModuleList;

/**
 * Command for marshaling proto files from modules
 */
class ProtoMarshalCommand extends Command
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var ModuleList
     */
    private $moduleList;
    /**
     * @var Dir
     */
    private $dir;

    /**
     * ProtoMarshalCommand constructor.
     * @param Filesystem $fileSystem
     * @param ModuleList $moduleList
     * @param Dir $dir
     */
    public function __construct(
        Filesystem $fileSystem,
        ModuleList $moduleList,
        Dir $dir,
        \Symfony\Component\Console\Input\ArgvInput $input,
        \Symfony\Component\Console\Output\ConsoleOutput $output
    ) {
        parent::__construct();

        $this->fileSystem = $fileSystem;
        $this->moduleList = $moduleList;
        $this->dir = $dir;

        /**
         * Injecting in setup:di:compile command
         * This is required because setup:di:compile command cleans the generated directory.
         * However, gRPC generated interfaces are required for this command
         *
         * TODO: better support for symfony command abbreviations
         * TODO: probably we need to have a dedicated extension point in setup:di:compile for code generation
         */
        if (\Magento\Setup\Console\Command\DiCompileCommand::NAME == $input->getFirstArgument()) {
            $output->writeln("Started gRPC code generation...");
            try {
                $this->execute($input, $output);
            } catch (\Exception $e) {
                $errorOutput = $output->getErrorOutput();
                $errorOutput->writeln('<error>' . $e->getMessage() . '</error>');
                exit(117);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('proto:marshal')->setDescription(
            'Extracts proto files from modules and place in location visible to gRPC server'
        );
    }

    /**
     * Returns the absolute path for the binary if found
     *
     * @param string $binaryName
     *
     * @throws \RuntimeException if binary is not found
     * @return string
     */
    private function getBinaryPath(string $binaryName): string {
        $paths = explode(':', $_SERVER['PATH']);
        foreach ($paths as $path) {
            $filePath = $path . DIRECTORY_SEPARATOR . $binaryName;
            if (file_exists($filePath)) {
                return $filePath;
            }
        }

        throw new \RuntimeException(
            $binaryName . ' binary is missing or not in include path.'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $directoryWrite = $this->fileSystem->getDirectoryWrite(DirectoryList::ROOT);
        //Dependencies validation
        $protocBinary = $this->getBinaryPath('protoc');
        $output->writeln("<info>protoc binary found in $protocBinary.</info>");

        $phpGrpcBinary = $this->getBinaryPath('protoc-gen-php-grpc');
        $output->writeln("<info>protoc-gen-php-grpc binary found in $phpGrpcBinary</info>");

        $phpGrpcPlugin = $this->getBinaryPath('grpc_php_plugin');
        $output->writeln("<info>grpc_php_plugin found in $phpGrpcPlugin</info>");

        //Generating PHP
        $protoFiles = $this->generatePhpClasses(
            $directoryWrite->getAbsolutePath(),
            $protocBinary,
            $phpGrpcBinary,
            $phpGrpcPlugin,
            $output
        );

        $mainProtoTemplate = "syntax = \"proto3\";\n";
        foreach ($protoFiles as $file) {
            $fileString = preg_replace("~^{$directoryWrite->getAbsolutePath()}~", '', $file);
            $mainProtoTemplate .= 'import public "' . $fileString . '";' . PHP_EOL;
        }

        $directoryWrite->writeFile(
            $directoryWrite->getAbsolutePath() . 'magento.proto',
            $mainProtoTemplate,
            'w'
        );
        $output->writeln(
            "<info>" .$directoryWrite->getAbsolutePath() . "magento.proto file is created."
            . " Launch gRPC server using <fg=magenta>rr-grpc serve -v</></info>"

        );
    }

    /**
     * Go through all modules and find corresponding files of active modules
     *
     * @param string $rootDirectory
     * @param string $protocBinary
     * @param string $phpGrpcBinary
     * @param string $phpGrpcPlugin
     * @param OutputInterface $output
     *
     * @return string[] Array of proto files found in Magento
     */
    private function generatePhpClasses(
        string $rootDirectory,
        string $protocBinary,
        string $phpGrpcBinary,
        string $phpGrpcPlugin,
        OutputInterface $output
    ) {
        //Collecting complete list of module's directories which contain proto files
        $includes = [];
        $protos = [];
        foreach ($this->moduleList->getNames() as $moduleName) {
            $moduleEtcDir = $this->dir->getDir($moduleName, DIR::MODULE_ETC_DIR);
            $currentDirectory = $this->fileSystem->getDirectoryReadByPath($moduleEtcDir);
            foreach ($currentDirectory->search('*.proto') as $file) {
                $includes[] = $currentDirectory->getAbsolutePath();
                $protos[] = $currentDirectory->getAbsolutePath() . $file;
            }
        }
        $includes = array_unique($includes);
        $includesStr = '-I=' . implode(' -I=', $includes);

        $protoStr = implode(' ', $protos);

        if (!$protoStr) {
            $output->writeln('No proto files detected. Existing');
            exit(0);
        }


        $command = "$protocBinary $includesStr --php_out={$rootDirectory}" . DirectoryList::GENERATED . "/code/"
            . " --php-grpc_out={$rootDirectory}" . DirectoryList::GENERATED . "/code/"
            . " --grpc_out={$rootDirectory}" . DirectoryList::GENERATED . "/code/"
            . " --descriptor_set_out={$rootDirectory}magento.protoset"
            . " --plugin=protoc-gen-php-grpc=$phpGrpcBinary"
            . " --plugin=protoc-gen-grpc=$phpGrpcPlugin"
            . " --include_imports"
            . " --include_source_info"
            . " $protoStr";

        $output->writeln("<info>Parsing protobuf files</info>");

        $out = '';
        exec($command, $out, $code);

        if ($code !== 0) {
            throw new \RuntimeException("Can't execute '$command' with output '"
                . implode("\n", $out) . "'"
            );
        }

        file_put_contents(
            $rootDirectory . DirectoryList::GENERATED . '/code/grpc_services_map.php',
            "<?php\nreturn " . var_export($this->findServices($rootDirectory), true) . ";"
        );
        $output->writeln(
            "<info>Services map is dumped in {$rootDirectory}"
            . DirectoryList::GENERATED . "/code/grpc_services_map.php</info>"
        );

        return $protos;
    }

    private function findServices($rootDirectory)
    {
        $services = [];
        $filesIterator = new \RecursiveDirectoryIterator($rootDirectory . DirectoryList::GENERATED . '/code');
        /** @var \SplFileInfo $file */
        foreach(new \RecursiveIteratorIterator($filesIterator) as $file)
        {
            $realPath = $file->getRealPath();
            if (!$realPath) {
                continue;
            }

            if (false !== strpos($realPath, 'Interface.php')) {
                $content = file_get_contents($realPath);
                if (preg_match(
                    '~namespace ([^;]+).*interface ([^ ]+) extends GRPC\\\ServiceInterface~si',
                    $content, $matches
                )) {
                    $services[] = $matches[1] . '\\' . $matches[2];

                }
            }

        }

        return $services;
    }
}
