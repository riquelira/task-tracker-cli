<?php

namespace TaskTracker\Infra\Storage;

use TaskTracker\Infra\Storage\Enum\StorageType;

class XmlFileStorage extends FileStorage
{
    private string $rootTag;
    private string $itemTag;

    public function __construct(string $path, string $rootTag, string $itemTag)
    {
        $this->rootTag = $rootTag;
        $this->itemTag = $itemTag;
        parent::__construct($path);
    }

    protected function storageType(): StorageType
    {
        return StorageType::XML;
    }

    public function persist(array $data): void
    {
        $this->writeStorage($this->mapToXml($data));
    }

    public function load(): array
    {
        $xml = @simplexml_load_file($this->storagePath);

        if ($xml === false)
            return [];

        $items = [];
        foreach ($xml->{$this->itemTag} as $node) {
            $item = [];
            foreach ($node->children() as $key => $value) {
                $item[$key] = (string) $value;
            }
            $items[] = $item;
        }

        return $items;
    }

    private function mapToXml(array $data): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $this->rootTag . '/>');

        foreach ($data as $item) {
            $itemNode = $xml->addChild($this->itemTag);

            foreach ($item as $key => $value) {
                $itemNode->addChild($key, htmlspecialchars((string) $value));
            }
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml->asXML());

        $serialized = $dom->saveXML();

        if ($serialized === false || !$this->isValidXml($serialized))
            throw new \RuntimeException('Error generating XML');

        return $serialized;
    }

    private function isValidXml(string $xml): bool
    {
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $doc = simplexml_load_string($xml);
        $ok = $doc !== false && libxml_get_errors() === [];

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $ok;
    }
}