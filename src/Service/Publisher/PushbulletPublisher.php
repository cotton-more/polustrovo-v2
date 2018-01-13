<?php

namespace Polustrovo\Service\Publisher;

use Polustrovo\Entity\ScreenshotSend;
use Polustrovo\Exception\PublisherException;
use Polustrovo\Repository\ScreenshotRepository;
use Psr\Log\LoggerInterface;
use Pushbullet\Exceptions\ConnectionException;
use Pushbullet\Exceptions\FilePushException;
use Pushbullet\Exceptions\InvalidTokenException;
use Pushbullet\Exceptions\NotFoundException;
use Pushbullet\Exceptions\NotPushableException;
use Pushbullet\Pushbullet;

class PushbulletPublisher implements Publishable
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ScreenshotRepository */
    private $screenshotRepository;

    /** @var Pushbullet */
    private $pushbullet;

    public function __construct(
        LoggerInterface $logger,
        ScreenshotRepository $screenshotRepository,
        Pushbullet $pushbullet
    ) {
        $this->logger = $logger;
        $this->screenshotRepository = $screenshotRepository;
        $this->pushbullet = $pushbullet;
    }

    public function getName(): string
    {
        return 'pushbullet';
    }

    /**
     * @param ScreenshotSend $screenshotPublish
     * @throws PublisherException
     */
    public function send(ScreenshotSend $screenshotPublish)
    {
        $screenshot = $this->screenshotRepository->getById($screenshotPublish->screenshotId());

        $photo = getenv('SCREENSHOTS_DIR').'/'.$screenshot->filename();

        try {
            $channel = $this->pushbullet->channel(getenv('PUSHBULLET_CHANNEL'));
            $channel->pushFile($photo);
            $this->logger->debug('Photo pushed');
        } catch (ConnectionException $e) {
            throw new PublisherException($e->getMessage(), $e->getCode(), $this, $e);
        } catch (InvalidTokenException $e) {
            throw new PublisherException($e->getMessage(), $e->getCode(), $this, $e);
        } catch (FilePushException $e) {
            throw new PublisherException($e->getMessage(), $e->getCode(), $this, $e);
        } catch (NotFoundException $e) {
            throw new PublisherException($e->getMessage(), $e->getCode(), $this, $e);
        } catch (NotPushableException $e) {
            throw new PublisherException($e->getMessage(), $e->getCode(), $this, $e);
        }
    }
}