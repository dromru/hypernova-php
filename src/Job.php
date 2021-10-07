<?php

namespace WF\Hypernova;

final class Job implements \JsonSerializable
{
    public string $name = '';
    public ?array $data = null;

    /**
     * @var mixed
     */
    public $metadata;

    public function __construct(string $name, ?array $data = null, array $metadata = [])
    {
        $this->name = $name;
        $this->data = $data;
        $this->metadata = $metadata;
    }

    /**
     * Factory to create from ['viewName' => ['name' => $name, 'data' => $data, 'metadata' => $metadata]]
     *
     * @param array $arr input array
     */
    public static function fromArray(array $arr): self
    {
        if (empty($arr['name']) || !isset($arr['data'])) {
            throw new \InvalidArgumentException('malformed job');
        }
        $metadata = isset($arr['metadata']) ? $arr['metadata'] : [];
        return new self($arr['name'], $arr['data'], $metadata);
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'data' => $this->data,
            'metadata' => $this->metadata
        ];
    }
}
