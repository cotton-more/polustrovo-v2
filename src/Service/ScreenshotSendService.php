<?php

declare(strict_types=1);

namespace Polustrovo\Service;

use ParagonIE\EasyDB\Exception\QueryError;
use Polustrovo\Entity\Screenshot;
use Polustrovo\Entity\ScreenshotSend;
use Polustrovo\Exception\PublisherException;
use Polustrovo\Repository\ScreenshotSendRepository;
use Polustrovo\Service\Publisher\Publishable;

class ScreenshotSendService
{
    const MINIMUM_FILE_SIZE = 90000; // 90 kB

    /** @var Publishable[] */
    private $publishers = [];

    /** @var ScreenshotSendRepository */
    private $screenshotSendRepository;

    /** @var array */
    private $enabledPublishers;

    public function __construct(
        ScreenshotSendRepository $screenshotSendRepository,
        array $enabledPublishers = []
    ) {
        $this->screenshotSendRepository = $screenshotSendRepository;
        $this->enabledPublishers = $enabledPublishers;
    }

    public function addPublisher(Publishable $publisher)
    {
        if (in_array($publisher->getName(), $this->enabledPublishers, true)) {
            $this->publishers[ $publisher->getName() ] = $publisher;
        }
    }

    /**
     * @return Publishable[]
     */
    public function publishers(): array
    {
        return $this->publishers;
    }

    /**
     * @param Screenshot $screenshot
     */
    public function publish(Screenshot $screenshot)
    {
        // do not add a screenshot if it less than minimum file size
        if ($screenshot->fileSize() < self::MINIMUM_FILE_SIZE) {
            return;
        }

        foreach ($this->publishers() as $publisher) {
            $this->screenshotSendRepository->addToSend($screenshot, $publisher->getName());
        }
    }

    public function sendAll()
    {
        foreach ($this->screenshotSendRepository->getUnsent() as $screenshotSendEntity) {
            $this->send($screenshotSendEntity);
        }
    }

    /**
     * @param ScreenshotSend $screenshotSendEntity
     * @param Publishable $publishable
     * @return bool
     */
    public function canSendWithPublisher(ScreenshotSend $screenshotSendEntity, Publishable $publishable)
    {
        if ($screenshotSendEntity->publisher() === $publishable->getName()) {
            return true;
        }

        return false;
    }

    private function send(ScreenshotSend $screenshotSendEntity)
    {
        foreach ($this->publishers() as $publisher) {
            if (!$this->canSendWithPublisher($screenshotSendEntity, $publisher)) {
                continue;
            }

            try {
                $publisher->send($screenshotSendEntity);

                $screenshotSendEntity = $screenshotSendEntity->with([
                    'sentAt' => 'now',
                ]);

                $this->screenshotSendRepository->setAsSent($screenshotSendEntity);
            } catch (PublisherException $exception) {
                $message = $exception->getPublisherName().': '.$exception->getMessage();

                $screenshotSendEntity = $screenshotSendEntity->with([
                    'errorMessage' => $message,
                ]);
            } catch (\InvalidArgumentException $e) {
            } catch (QueryError $e) {
            }
        }
    }
}