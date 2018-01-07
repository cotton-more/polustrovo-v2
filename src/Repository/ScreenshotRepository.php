<?php

declare(strict_types=1);

namespace Polustrovo\Repository;

use BrowshotAPI\Message\ScreenshotStatus;
use ParagonIE\EasyDB\EasyDB;
use ParagonIE\EasyDB\EasyStatement;
use Polustrovo\Entity\Screenshot;
use Polustrovo\Entity\ScreenshotId;

class ScreenshotRepository
{
    /** @var EasyDB */
    private $easyDB;

    public function __construct(EasyDB $easyDB)
    {
        $this->easyDB = $easyDB;
    }

    /**
     * @param Screenshot $screenshot
     * @return false|ScreenshotId
     */
    public function create(Screenshot $screenshot)
    {
        $screenshotId = $this->nextIdentity();

        $result = $this->easyDB->insert('screenshot', [
            'screenshot_id' => $screenshotId->id(),
            'browshot_id' => $screenshot->browshotId(),
            'browshot_instance' => $screenshot->browshotInstance(),
            'url' => $screenshot->url(),
            'status' => $screenshot->status(),
            'error_message' => $screenshot->errorMessage(),
        ]);

        return $result ? $screenshotId : false;
    }

    /**
     * @return ScreenshotId
     */
    public function nextIdentity()
    {
        return new ScreenshotId();
    }

    /**
     * @return Screenshot[]
     */
    public function getQueued()
    {
        $statement = EasyStatement::open()
            ->in('status NOT IN (?)', [ScreenshotStatus::ERROR])
            ->with('filename IS NULL')
        ;

        $sql = <<<SQL
SELECT 
  screenshot_id,
  browshot_id,
  browshot_instance,
  url,
  status,
  error_message,
  filename,
  file_size,
  created_at
FROM screenshot
WHERE {$statement->sql()}
SQL;

        $rows = $this->easyDB->run($sql, ...$statement->values());

        $result = array_map(function ($row) {
            return Screenshot::create([
                'screenshotId' => $row['screenshot_id'],
                'browshotId' => $row['browshot_id'],
                'browshotInstance' => $row['browshot_instance'],
                'url' => $row['url'],
                'status' => $row['status'],
                'errorMessage' => $row['error_message'],
            ]);
        }, $rows);

        return $result;
    }

    public function saveFileAndStatus(Screenshot $screenshot)
    {
        $result = $this->easyDB->update('screenshot', [
            'status' => $screenshot->status(),
            'filename' => $screenshot->filename(),
            'file_size' => $screenshot->fileSize(),
        ], [
            'screenshot_id' => $screenshot->screenshotId()->id(),
        ]);


        return $result;
    }

    /**
     * @param ScreenshotId $screenshotId
     * @return Screenshot
     */
    public function getById(ScreenshotId $screenshotId)
    {
        $sql = <<<SQL
SELECT * FROM screenshot WHERE screenshot_id = ?
SQL;

        $row = $this->easyDB->row($sql, $screenshotId->id());

        return Screenshot::create($row);
    }

    public function updateStatus(Screenshot $screenshot, $errorMessage = null)
    {
        $changes = [
            'status' => $screenshot->status(),
        ];

        if ($errorMessage) {
            $changes['error_message'] = $errorMessage;
        }

        $result = $this->easyDB->update('screenshot', $changes, [
            'screenshot_id' => $screenshot->screenshotId()->id(),
        ]);

        return $result;
    }
}
