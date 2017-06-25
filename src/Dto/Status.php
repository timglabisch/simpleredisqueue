<?php

namespace Tg\RedisQueue\Dto;


use Tg\RedisQueue\Status\StatusEntry;

class Status
{
    /** @var bool */
    private $unknown;

    /** @var StatusEntry[] */
    private $entries = [];

    public function __construct(array $entries, $unknown = false)
    {
        $this->entries = array_reverse($entries);
        $this->unknown = $unknown;
    }

    public static function newUnkownStatus(): Status
    {
        return new self([], true);
    }

    public function getLatestEntryWithIdentifier(string $identifier, $default = null)
    {
        foreach ($this->entries as $entry) {
            if ($entry->getIdentifier() === $identifier) {
                return $entry;
            }
        }

        return $default;
    }

    /**
     * @return StatusEntry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

}