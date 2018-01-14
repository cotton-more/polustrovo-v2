<?php

declare(strict_types=1);

namespace Polustrovo\Repository;

use ParagonIE\EasyDB\EasyDB;
use Polustrovo\Entity\Screenshot;
use Polustrovo\Entity\ScreenshotSend;
use Polustrovo\Entity\ScreenshotSendId;

class ScreenshotSendRepository
{
    private $easyDB;
    private $tableName = 'screenshot_send';

    public function __construct(EasyDB $easyDB)
    {
        $this->easyDB = $easyDB;
    }

    /**
     * @return ScreenshotSendId
     */
    public function nextIdentity()
    {
        return new ScreenshotSendId();
    }

    /**
     * @param Screenshot $screenshot
     * @param string $publisher
     * @return false|ScreenshotSendId
     */
    public function addToSend(Screenshot $screenshot, string $publisher)
    {
        $id = $this->nextIdentity();

        $result = $this->easyDB->insert($this->tableName, [
            'screenshot_send_id' => $id->id(),
            'screenshot_id' => $screenshot->screenshotId()->id(),
            'publisher' => $publisher,
            'sent_at' => null,
            'error_message' => '',
        ]);

        return $result ? $id : false;
    }

    /**
     * @return ScreenshotSend[]
     */
    public function getUnsent()
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE sent_at IS NULL";

        $rows = $this->easyDB->run($sql);

        $result = array_map(function ($row) {
            return ScreenshotSend::create([
                'screenshotSendId' => $row['screenshot_send_id'],
                'screenshotId' => $row['screenshot_id'],
                'publisher' => $row['publisher'],
                'errorMessage' => (string) $row['error_message'],
                'createdAt' => $row['created_at'],
            ]);
        }, $rows);

        return $result;
    }

    public function setAsSent(ScreenshotSend $screenshotSend)
    {
        if (!in_array('sentAt', $screenshotSend->changes())) {
            throw new \InvalidArgumentException('A sentAt property has to be changed');
        }

        $sentAt = $screenshotSend->sentAt();

        $result = $this->easyDB->update($this->tableName, [
            'sent_at' => $sentAt->format('Y-m-d H:i:s'),
            'error_message' => $screenshotSend->errorMessage(),
        ], [
            'screenshot_send_id' => $screenshotSend->screenshotSendId()->id(),
        ]);

        return $result;
    }
}
