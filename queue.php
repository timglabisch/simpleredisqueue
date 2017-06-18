<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;


if (!version_compare(phpversion(), "5.5.9", '>=')) {
    echo 'Required at least PHP version 5.5.9, your version: ' . PHP_VERSION . "\n";
    die(1);
}

(new XmlFileLoader($container = new ContainerBuilder(), new FileLocator(__DIR__)))->load(__DIR__. '/services.xml');

$container->set('container', $container);

$application = new Application();
$application->add($container->get('command_producer'));
$application->add($container->get('command_consumer'));
$application->add($container->get('command_schedule'));
$application->setDefaultCommand('consumer');
$application->run();