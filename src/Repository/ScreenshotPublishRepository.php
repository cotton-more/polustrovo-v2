<?php

namespace Polustrovo\Repository;

use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\EasyStatement;
use Polustrovo\Entity\Screenshot;
use Polustrovo\Entity\ScreenshotId;
use Polustrovo\Entity\ScreenshotPublish;
use Polustrovo\Entity\ScreenshotPublishId;

class ScreenshotPublishRepository
{
    private $easyDB;

    public function __construct(EasyDB $easyDB)
    {
        $this->easyDB = $easyDB;
    }

    /**
     * @return ScreenshotPublishId
     */
    public function nextIdentity()
    {
        return new ScreenshotPublishId();
    }

    /**
     * @param Screenshot $screenshot
     * @param string $publisher
     * @return false|ScreenshotPublishId
     */
    public function addToPublish(Screenshot $screenshot, string $publisher)
    {
        $id = $this->nextIdentity();

        $result = $this->easyDB->insert('screenshot_publish', [
            'screenshot_publish_id' => $id->id(),
            'screenshot_id' => $screenshot->screenshotId()->id(),
            'publisher' => $publisher,
        ]);

        return $result ? $id : false;
    }

    /**
     * @return ScreenshotPublish[]
     */
    public function getUnpublished()
    {
        $sql = <<<SQL
SELECT sp.* FROM screenshot_publish sp WHERE published_at IS NULL
SQL;

        $rows = $this->easyDB->run($sql);

        $result = array_map(function ($row) {
            return ScreenshotPublish::create([
                'screenshotPublishId' => $row['screenshot_publish_id'],
                'screenshotId' => $row['screenshot_id'],
                'publisher' => $row['publisher'],
            ]);
        }, $rows);

        return $result;
    }

    /**
     * @param ScreenshotId[] $screenshotIdList
     * @return ScreenshotPublish[]
     */
    public function getListByScreenshotId(array $screenshotIdList)
    {
        if (count($screenshotIdList) === 0) {
            throw new \InvalidArgumentException('Screenshot is list could not be empty');
        }

        $screenshotIdList = array_map(function (ScreenshotId $screenshotId) {
            return $screenshotId->id();
        }, $screenshotIdList);

        $statement = EasyStatement::open()
            ->in('screenshot_id IN (?*)', $screenshotIdList)
        ;

        $sql = <<<SQL
SELECT sp.* FROM screenshot_publish sp WHERE {$statement->sql()}
SQL;

        $rows = $this->easyDB->run($sql, ...$screenshotIdList);

        $result = array_map(function ($row) {
            return ScreenshotPublish::create([
                'screenshotPublishId' => $row['screenshot_publish_id'],
                'publisher' => $row['publisher'],
            ]);
        }, $rows);

        return $result;
    }

    public function setAsPublished(ScreenshotPublish $screenshotPublish)
    {
        if (!in_array('publishedAt', $screenshotPublish->changes())) {
            throw new \InvalidArgumentException('A publishedAt property has to be changed');
        }

        $publishedAt = $screenshotPublish->publishedAt();

        $result = $this->easyDB->update('screenshot_publish', [
            'published_at' => $publishedAt->format('Y-m-d H:i:s'),
            'error_message' => $screenshotPublish->errorMessage() ?: '',
        ], [
            'screenshot_publish_id' => $screenshotPublish->screenshotPublishId()->id(),
        ]);

        return $result;
    }
}
