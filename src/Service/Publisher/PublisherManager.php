<?php

namespace Polustrovo\Service\Publisher;

use Polustrovo\Entity\Screenshot;
use Polustrovo\Entity\ScreenshotPublish;
use Polustrovo\Exception\PublisherException;
use Polustrovo\Repository\ScreenshotPublishRepository;

class PublisherManager
{
    const MINIMUM_FILE_SIZE = 20480; // 20 kb

    /** @var Publishable[] */
    private $publishers = [];

    /** @var ScreenshotPublishRepository */
    private $screenshotPublishRepository;

    /** @var array */
    private $enabledPublishers;

    public function __construct(
        ScreenshotPublishRepository $screenshotPublishRepository,
        array $enabledPublishers = []
    ) {
        $this->screenshotPublishRepository = $screenshotPublishRepository;
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
            $this->screenshotPublishRepository->addToPublish($screenshot, $publisher->getName());
        }
    }

    public function sendAll()
    {
        $screenshotPublishList = $this->screenshotPublishRepository->getUnpublished();

        foreach ($screenshotPublishList as $screenshotPublish) {
            $this->send($screenshotPublish);
        }
    }

    /**
     * @param ScreenshotPublish $screenshotPublish
     * @param Publishable $publishable
     * @return bool
     */
    public function canSendWithPublisher(ScreenshotPublish $screenshotPublish, Publishable $publishable)
    {
        if ($screenshotPublish->publisher() === $publishable->getName()) {
            return true;
        }

        return false;
    }

    private function send(ScreenshotPublish $screenshotPublish)
    {
        foreach ($this->publishers() as $publisher) {
            if (!$this->canSendWithPublisher($screenshotPublish, $publisher)) {
                continue;
            }

            try {
                $publisher->send($screenshotPublish);
            } catch (PublisherException $exception) {
                $message = $exception->getPublisherName().': '.$exception->getMessage();

                $screenshotPublish = $screenshotPublish->with([
                    'errorMessage' => $message,
                ]);
            }

            $screenshotPublish = $screenshotPublish->with([
                'publishedAt' => 'now',
            ]);

            $this->screenshotPublishRepository->setAsPublished($screenshotPublish);
        }
    }
}
