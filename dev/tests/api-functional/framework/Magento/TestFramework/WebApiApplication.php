<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework;

/**
 * Override \Magento\TestFramework\WebApiApplication to be able provide install configuration with empty values
 *
 * Should be eliminated after resolving MC-32269 for 2.4
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class WebApiApplication extends Application
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        throw new \Exception(
            "Can't start application: purpose of Web API Application is to use classes and models from the application"
            . " and don't run it"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function install($cleanup)
    {
        if ($cleanup) {
            $this->cleanup();
        }

        $installOptions = $this->getInstallConfig();

        /* Install application */
        if ($installOptions) {
            $installCmd = 'php -f ' . BP . '/bin/magento setup:install -vvv';
            $installArgs = [];
            foreach ($installOptions as $optionName => $optionValue) {
                if (is_bool($optionValue)) {
                    if (true === $optionValue) {
                        $installCmd .= " --$optionName";
                    }
                    continue;
                }
                $installCmd .= " --$optionName=%s";
                $installArgs[] = $optionValue;
            }
            $this->_shell->execute($installCmd, $installArgs);
        }
    }

    /**
     * Use the application as is
     *
     * {@inheritdoc}
     */
    protected function getCustomDirs()
    {
        return [];
    }
}
