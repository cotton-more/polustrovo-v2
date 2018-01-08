<?php

use Polustrovo\Service\Publisher\PushbulletPublisher;
use Polustrovo\Service\Publisher\TelegramPublisher;
use Polustrovo\Service\ScreenshotSendService;

return [
    \ParagonIE\EasyDB\EasyDB::class => \DI\factory(function ($dsn) {
        return \ParagonIE\EasyDB\Factory::create($dsn);
    })->parameter('dsn', \DI\env('DATABASE_DSN')),

    \Psr\Log\LoggerInterface::class => \DI\factory(function ($filename) {
        $logger = new \Monolog\Logger('polustrovo-v2');

        $syslogHandler = new \Monolog\Handler\SyslogHandler('polustrovo-v2');
        $syslogHandler->setFormatter(new \Monolog\Formatter\JsonFormatter);
        $logger->pushHandler($syslogHandler);

        $rotatingFileHandler = new \Monolog\Handler\RotatingFileHandler($filename);
        $rotatingFileHandler->setFormatter(new \Monolog\Formatter\JsonFormatter);
        $logger->pushHandler($rotatingFileHandler);

        $streamHandler = new \Monolog\Handler\StreamHandler('php://stdout');
        $streamHandler->setFormatter(new \Monolog\Formatter\LineFormatter);
        $logger->pushHandler($streamHandler);

        return $logger;
    })->parameter('filename', \DI\env('LOG_FILENAME')),

    \GuzzleHttp\ClientInterface::class =>
        \DI\factory(function($apiUrl, $apiKey) {
            $client = new \GuzzleHttp\Client([
                'base_uri' => $apiUrl,
                \GuzzleHttp\RequestOptions::QUERY => [
                    'key' => $apiKey,
                ],
            ]);

            return $client;
        })
        ->parameter('apiUrl', \DI\env('BROWSHOT_API_URL'))
        ->parameter('apiKey', \DI\env('BROWSHOT_API_KEY'))
    ,

    ScreenshotSendService::class => \DI\create(ScreenshotSendService::class)
        ->constructor(
            \DI\get(\Polustrovo\Repository\ScreenshotPublishRepository::class),
            \DI\value(['pushbullet', 'telegram'])
        )
        ->method('addPublisher', \DI\get(TelegramPublisher::class))
        ->method('addPublisher', \DI\get(PushbulletPublisher::class))
    ,

    \TelegramBot\Api\BotApi::class => \DI\create(\TelegramBot\Api\BotApi::class)
        ->constructor(\DI\env('TELEGRAM_BOT_TOKEN'))
    ,

    \Pushbullet\Pushbullet::class => \DI\create(\Pushbullet\Pushbullet::class)
        ->constructor(\DI\env('PUSHBULLET_API_KEY'))
    ,
];