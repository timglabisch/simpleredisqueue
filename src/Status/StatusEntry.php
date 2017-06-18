<?php

namespace Tg\RedisQueue\Status;

class StatusEntry
{
    const STATUS_CREATED = 'CREATED';

    /** @var string */
    private $identifier;

    /** @var string */
    private $value;

    /** @var \DateTime */
    private $time;

    public function __construct(string $identifier, string $value, \DateTime $time)
    {
        if (strpos($identifier, '|') !== false) {
            throw new \InvalidArgumentException("identifier must not contain char '|'");
        }

        $this->identifier = $identifier;
        $this->value = $value;
        $this->time = $time;
    }

    public static function fromString(string $string)
    {
        $res = explode('|', $string, 3);

        if (count($res) != 3) {
            throw new \InvalidArgumentException();
        }

        return new self($res[1], $res[2], \DateTime::createFromFormat('U', $res[0]));
    }

    public function __toString()
    {
        return $this->time->getTimestamp() . '|' . $this->identifier . '|' . $this->value;
    }


    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getTime(): \DateTime
    {
        return $this->time;
    }
}