<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogStorefrontConnector\Command;

use Composer\Config as ComposerConfig;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Module\Dir;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Module\ModuleList;

/**
 * Command for grpc server and grpc_services_map initialization
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grpc extends Command
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
     * @var string
     */
    private $moduleName = 'Magento_Grpc';

    /**
     * @var ComposerConfig
     */
    private $composerConfig;

    /**
     * @var string[]
     */
    private $filesCopyToVendorBin = [
        'grpc-server',
        'grpc-workers',
        'worker',
    ];

    /**
     * @param Filesystem $fileSystem
     * @param ModuleList $moduleList
     * @param Dir $dir
     * @param ComposerConfig $composerConfig
     */
    public function __construct(
        Filesystem $fileSystem,
        ModuleList $moduleList,
        Dir $dir,
        ComposerConfig $composerConfig
    ) {
        parent::__construct();
        $this->fileSystem = $fileSystem;
        $this->moduleList = $moduleList;
        $this->dir = $dir;
        $this->composerConfig = $composerConfig;
    }

    /**
     * @inheritDoc
     */
    protected function configure()
    {
        $this->setName('storefront:grpc:init')->setDescription(
            'Initializes gRPC server and services map'
        );
    }

    /**
     * @inheritDoc
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var WriteInterface $directoryWrite */
        $directoryWrite = $this->fileSystem->getDirectoryWrite(DirectoryList::ROOT);
        $moduleRoot = $this->dir->getDir($this->moduleName);
        /** @var DriverInterface $writeDriver */
        $writeDriver = $directoryWrite->getDriver();

        $this->copyToVendorBin($writeDriver, $output, $moduleRoot);
        $this->createDefaultServiceMap($writeDriver, $output);

        return 0;
    }

    /**
     * Copies files to vendor/bin folder
     *
     * @param DriverInterface $writeDriver
     * @param OutputInterface $output
     * @param string $moduleRoot
     * @throws FileSystemException
     */
    private function copyToVendorBin(
        DriverInterface $writeDriver,
        OutputInterface $output,
        string $moduleRoot
    ): void {
        $vendorFolder = $this->composerConfig->get('vendor-dir');

        foreach ($this->filesCopyToVendorBin as $filename) {
            $moduleBinPath = $moduleRoot . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . $filename;
            $vendorBinPath = BP . $vendorFolder
                . DIRECTORY_SEPARATOR . 'bin'
                . DIRECTORY_SEPARATOR . $filename;
            $this->copyFileToVendorBin($writeDriver, $output, $moduleBinPath, $vendorBinPath);
        }
    }

    /**
     * Copy bin file from module to vendor.
     *
     * @param DriverInterface $writeDriver
     * @param OutputInterface $output
     * @param string $source
     * @param string $destination
     * @throws FileSystemException
     */
    private function copyFileToVendorBin(
        DriverInterface $writeDriver,
        OutputInterface $output,
        string $source,
        string $destination
    ) {
        if (!$writeDriver->isExists($destination) && $writeDriver->isExists($source)) {
            if (!$writeDriver->isExists($writeDriver->getParentDirectory($destination))) {
                $writeDriver->createDirectory($writeDriver->getParentDirectory($destination));
            }
            $writeDriver->copy($source, $destination);
            $writeDriver->changePermissions($destination, 0666);
            $output->writeln(
                \sprintf('<info>"%s" successfully copied to bin folder</info>', $destination)
            );
        } else {
            $output->writeln(
                \sprintf('<info>"%s" already exists</info>', $destination)
            );
        }
    }

    /**
     * Create default service map.
     *
     * @param Filesystem\DriverInterface $writeDriver
     * @param OutputInterface $output
     * @throws FileSystemException
     */
    private function createDefaultServiceMap(
        DriverInterface $writeDriver,
        OutputInterface $output
    ): void {
        $servicesFile = BP . DIRECTORY_SEPARATOR . 'generated' . DIRECTORY_SEPARATOR .
            'code' . DIRECTORY_SEPARATOR . 'grpc_services_map.php';
        if (!$writeDriver->isExists($servicesFile)) {
            $content =
                <<<SERVICE
<?php
return [
    \Magento\CatalogStorefrontApi\Api\CatalogProxyServer::class
];

SERVICE;
            $writeDriver->touch($servicesFile);
            $resource = $writeDriver->fileOpen($servicesFile, 'wb');
            $writeDriver->fileWrite($resource, $content);
            $writeDriver->fileClose($resource);
            $output->writeln(
                \sprintf('<info>Services map is dumped in "%s"</info>', $servicesFile)
            );
        } else {
            $output->writeln(
                \sprintf('<info>Services map "%s" already exists</info>', $servicesFile)
            );
        }
    }
}
