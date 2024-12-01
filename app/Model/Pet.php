<?php

namespace App\Model;

use DOMDocument;
use Exception;
use SimpleXMLElement;

class Pet
{
    // rozsirit toto pole o nove atributy:
    const ID = 'id';
    const NAME = 'name';
    const CATEGORY = 'category';
    const PHOTOURLS = 'photoUrls';
    const TAGS = 'tags';
    const STATUS = 'status';

    //nove atributy
    const AGE = 'age';
    const OWNERS = 'owners';
    public static array $fillable = [
        self::ID,
        self::NAME,
        self::CATEGORY,
        self::PHOTOURLS,
        self::TAGS,
        self::STATUS,
        //nove atributy
        self::AGE,
        self::OWNERS
    ];

    const TAG = 'tag';
    const PHOTOURL = 'url';

    //novy pomocny atribut
    const OWNER = 'owner';

    const ARRAY_FIELDS = [
        self::TAGS,
        self::PHOTOURLS,
        //nove atributy
        self::OWNERS
    ];
    const SINGULAR_ELEMENTS = [
        self::TAGS => self::TAG,
        self::PHOTOURLS => self::PHOTOURL,
        //nove atributy
        self::OWNERS => self::OWNER
    ];

    //pridat nove atributy tu:
    public int $id;
    public string $name; // meno zvieratka
    public array $category; // objekt kategorie ['id' => int, 'name' => string]
    public array $photoUrls = []; // pole obrazkov (iba url)
    public array $tags = []; // pole tagov ['id' => int, 'name' => string]
    public string $status; // stav (available, sold...)

    //nove atributy
    public int $age; // vek zvieratka
    public array $owners = []; // pole majitelov


    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, self::$fillable)) {

                if (is_scalar($value)) {
                    $this->{$key} = $value;
                } elseif (is_array($value)) {
                    match ($key) {
                        //array elementy
                        self::TAGS,
                        self::OWNERS => $this->{$key} = array_map(fn($item) => $this->getFormattedElement($item), $value),
                        //objekt elementy
                        self::CATEGORY => $this->category = $this->getFormattedElement($value),
                        //specificke elementy
                        self::PHOTOURLS => $this->photoUrls = array_map('strval', $value),
                        //ostatne
                        default => array_map(function () use ($key, $value) {
                            foreach ($value as $nestedKey => $nestedValue) {
                                $this->{$key} = $nestedValue;
                            }
                        }, $value),

                    };
                } else {
                    // If the value is invalid, log or ignore it
                    throw new \InvalidArgumentException("Invalid value for {$key}");
                }
            }
        }
    }

    /**
     * @param $item
     * @return array
     */
    private function getFormattedElement($item): array
    {
        return [
            self::ID => (int)($item[self::ID] ?? 0),
            self::NAME => (string)($item[self::NAME] ?? ''),
        ];
    }


    /**
     * @param string $xml
     * @return string
     */
    protected function removeWhitespaceFromXml(string $xml): string
    {
        // Load the XML into a DOMDocument
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false; // Remove extra whitespace
        $dom->formatOutput = false;      // Ensure output is compact
        $dom->loadXML($xml);

        // Save the XML as a string
        return $dom->saveXML();
    }


    /**
     * @return array|string[]
     */
    public static function getFillable(): array
    {
        return self::$fillable;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $fillable = $this->getFillable();
        $data = [];

        foreach ($fillable as $field) {
            if (property_exists($this, $field)) {
                $data[$field] = $this->{$field};
            }
        }

        return $data;
    }


    /**
     * @return string
     */
    public function toXml(): string
    {
        $xml = new SimpleXMLElement('<pet/>');
        foreach (self::$fillable as $field) {
            if (isset($this->{$field})) {
                $value = $this->{$field};

                $methodName = 'serialize' . ucfirst($field);

                if (method_exists($this, $methodName)) {
                    $this->$methodName($xml, $value);
                } elseif (is_array($value)) {
                    $child = $xml->addChild($field);
                    $this->serializeNestedArray($child, $value);
                } else {
                    $xml->addChild($field, htmlspecialchars((string)$value));
                }
            }
        }
        return $xml->asXML();
    }

    /**
     * @param SimpleXMLElement $parent
     * @param array $array
     * @return void
     */
    private function serializeArray(SimpleXMLElement $parent, array $array): void
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $subChild = $parent->addChild($key); // nazov kluca ako tag
                $this->serializeArray($subChild, $value);
            } else {
                $parent->addChild($key, htmlspecialchars((string)$value)); // nazov kluca ako tag
            }
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param array $array
     * @return void
     */
    private function serializeNestedArray(SimpleXMLElement $xml, array $array): void
    {
        $childName = $xml->getName();
        if (in_array($childName, self::ARRAY_FIELDS)) {
            foreach ($array as $key => $value) {
                $singularKey = self::SINGULAR_ELEMENTS[$childName];
                $itemElement = $xml->addChild($singularKey);
                $this->serializeArray($itemElement, $value);
            }
        } else {
            $this->serializeArray($xml, $array);
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param array $tags
     * @return void
     */
    private function serializeTags(SimpleXMLElement $xml, array $tags): void
    {
        $tagsElement = $xml->addChild(self::TAGS);
        foreach ($tags as $tag) {
            $tagElement = $tagsElement->addChild('tag');
            $this->serializeArray($tagElement, $tag);
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param array $photoUrls
     * @return void
     */
    private function serializePhotoUrls(SimpleXMLElement $xml, array $photoUrls): void
    {
        $photoUrlsElement = $xml->addChild(self::PHOTOURLS);
        foreach ($photoUrls as $url) {
            $photoUrlsElement->addChild('url', htmlspecialchars((string)$url));
        }
    }


    /**
     * @param string $xmlString
     * @return Pet
     * @throws Exception
     */
    public static function fromXml(string $xmlString): self
    {
        $xml = new SimpleXMLElement($xmlString);
        $attributes = [];
        foreach ($xml as $key => $value) {
            if (in_array($key, self::$fillable)) {
                $attributes[$key] = json_decode(json_encode($value), true);
            }
        }
        return new self($attributes);
    }
}

