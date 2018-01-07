<?php

use Polustrovo\Command\ScreenshotDownloadCommand;
use Polustrovo\Command\ScreenshotSendCommand;
use Polustrovo\Command\ScreenshotTakeCommand;
use Symfony\Component\Dotenv\Dotenv;

require_once __DIR__.'/../vendor/autoload.php';

(new Dotenv())->load(__DIR__.'/../.env.dist', __DIR__.'/../.env');

$containerBuilder = new \DI\ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__.'/container.php');

$application = new \Silly\Application('Polustrovo', '0.0.1');
$application->useContainer($containerBuilder->build(), true, true);

$application->command(
    'screenshot:take url instanceId',
    ScreenshotTakeCommand::class,
    ['take']
);

$application->command(
    'screenshot:download',
    ScreenshotDownloadCommand::class,
    ['download']
);

$application->command(
    'screenshot:send',
    ScreenshotSendCommand::class,
    ['send']
);

return $application;