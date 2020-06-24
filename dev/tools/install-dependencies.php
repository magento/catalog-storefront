#!/usr/bin/env php
<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Ad-hoc solution to update required module dependencies for git-based installation.
 */

if (!isset($argv['1'])) {
    echo "Usage: \n";
    echo "./install-dependencies.php <path to Magento root>\n";
    exit(1);
}

$magentoDir = realpath($argv[1]);
$sourceDir = $magentoDir;
$packageTypes = ['magento2-module', 'magento2-theme', 'magento2-language', 'magento2-library'];
$preparePackagesScripts = [];
$repos = [];
$existingRepos = [];
$require = [];
$magentoDirStrLength = strlen($magentoDir . '/');

$projectComposerFile = $magentoDir . '/composer.json';
if (!file_exists($projectComposerFile)) {
    echo "cannot find root composer file in $projectComposerFile\n";
    exit(1);
}
$projectComposer = json_decode(file_get_contents($magentoDir . '/composer.json'), true);
if ($projectComposer['type'] !== 'project') {
    echo "wrong type for $projectComposerFile\n";
    exit(1);
}

echo "Update composer.json with modules dependencies in Magento project directory '$magentoDir'\n";

$existingRepos = $projectComposer['require'] ?? [];
$repoDirIterator = new RecursiveDirectoryIterator($magentoDir, \FilesystemIterator::FOLLOW_SYMLINKS);
$recursiveRepoDirIterator = new RecursiveIteratorIterator($repoDirIterator);
$regexIteratorExcludeTests = new RegexIterator($recursiveRepoDirIterator, '/^((?!test|dev).)*$/', RegexIterator::MATCH);
$regexIterator = new RegexIterator($regexIteratorExcludeTests, '/composer.json$/', RegexIterator::MATCH);
foreach ($regexIterator as $currentFileInfo) {

    $packageInfo = json_decode(file_get_contents($currentFileInfo->getPathName()), true);
    if (isset($packageInfo['type']) && isset($packageInfo['require']) && in_array($packageInfo['type'], $packageTypes, true)) {
        $repos[] = $packageInfo['require'];
    }
}
$repos = \array_unique(\array_merge(...$repos));
$repos = \array_diff($repos, $existingRepos);

if ($repos) {
    $projectComposer['require'] += $repos;

    file_put_contents($magentoDir . '/composer.json', str_replace('\/', '/', json_encode($projectComposer, JSON_PRETTY_PRINT)));

    shell_exec('cd ' . $magentoDir);
    shell_exec('composer update');
}

echo "Project composer.json updated.\n";