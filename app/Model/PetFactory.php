<?php

namespace App\Model;

use App\Model\Pet;
use DOMDocument;
use Exception;
use SimpleXMLElement;

final class PetFactory
{
    protected string $xmlFile;

    public function __construct(string $xmlFile)
    {
        $this->xmlFile = $xmlFile;

        if (!file_exists($this->xmlFile)) {
            $this->initializeXmlFile();
        }
    }

    /**
     * @return void
     */
    protected function initializeXmlFile(): void
    {
        $xml = new SimpleXMLElement('<pets/>');
        $xml->asXML($this->xmlFile);
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getAll(): array
    {
        $xml = simplexml_load_file($this->xmlFile);
        $pets = [];

        foreach ($xml->pet as $petElement) {
            $pets[] = Pet::fromXml($petElement->asXML());
        }

        return $pets;
    }


    /**
     * @param int $id
     * @return Pet|null
     * @throws Exception
     */
    public function find(int $id): ?Pet
    {
        $xml = simplexml_load_file($this->xmlFile);

        foreach ($xml->pet as $petElement) {
            if ((int)$petElement->id == $id) {
                return Pet::fromXml($petElement->asXML());
            }
        }

        return null;
    }

    /**
     * @param array $data
     * @return Pet
     * @throws Exception
     */
    public function create(array $data): Pet
    {
        $xml = simplexml_load_file($this->xmlFile);

        if (!isset($data['id'])) {
            $maxId = 0;
            foreach ($xml->pet as $petElement) {
                $currentId = (int)$petElement->id;
                if ($currentId > $maxId) {
                    $maxId = $currentId;
                }
            }
            $data['id'] = $maxId + 1;
        }

        $fillable = Pet::getFillable();
        $filteredData = array_filter(
            $data,
            fn($key) => in_array($key, $fillable, true),
            ARRAY_FILTER_USE_KEY
        );

        $pet = new Pet($filteredData);
        $this->addPetToXml($xml, $pet);
        $xml->asXML($this->xmlFile);

        return $pet;
    }

    public function update(int $id, array $data): array
    {
        $xml = simplexml_load_file($this->xmlFile);

        $pet = [];
        foreach ($xml->pet as $petElement) {
            if ((int)$petElement->id == $id) {
                $this->updatePetInXml($petElement, $data);
                $xml->asXML($this->xmlFile);
                $pet = Pet::fromXml($petElement->asXML())->toArray();
                break;
            }
        }

        return $pet;
    }

    public function delete(int $id): void
    {
        $xml = simplexml_load_file($this->xmlFile);

        foreach ($xml->pet as $index => $petElement) {
            if ((int)$petElement->id == $id) {
                $dom = dom_import_simplexml($petElement);
                $dom->parentNode->removeChild($dom);
                $dom->ownerDocument->save($this->xmlFile);
                break;
            }
        }
    }

    /**
     * @param SimpleXMLElement $xml
     * @param Pet $pet
     * @throws Exception
     */
    protected function addPetToXml(SimpleXMLElement $xml, Pet $pet): void
    {
        //<xml><pets><pet>...</pet></pets></xml>
        $petElement = $xml->addChild('pet');

        $petXml = new SimpleXMLElement($pet->toXml());
        $this->appendXmlElement($petElement, $petXml);
    }

    /**
     * @param SimpleXMLElement $target
     * @param SimpleXMLElement $source
     * @return void
     */
    private function appendXmlElement(SimpleXMLElement $target, SimpleXMLElement $source): void
    {
        foreach ($source->children() as $child) {
            $childCopy = $target->addChild($child->getName(), (string)$child);
            foreach ($child->attributes() as $attrName => $attrValue) {
                $childCopy->addAttribute($attrName, $attrValue);
            }

            if ($child->count()) {
                $this->appendXmlElement($childCopy, $child);
            }
        }
    }

    /**
     * @param SimpleXMLElement $petElement
     * @param array $data
     * @return void
     */
    protected function updatePetInXml(SimpleXMLElement $petElement, array $data): void
    {
        // Remove fields not present in $data
        foreach (Pet::$fillable as $key) {
            if (!array_key_exists($key, $data)) {
                unset($petElement->{$key});
            }
        }

        // Update or add fields present in $data
        foreach ($data as $key => $value) {
            if (in_array($key, Pet::$fillable)) {
                if (is_array($value)) {
                    // Remove existing child nodes for the array field
                    unset($petElement->{$key});
                    $child = $petElement->addChild($key);

                    if (in_array($key, Pet::ARRAY_FIELDS)) {
                        foreach ($value as $item) {
                            $elementName = Pet::SINGULAR_ELEMENTS[$key];
                            if (is_array($item)) {
                                // Handle nested objects (e.g., tags)
                                $itemElement = $child->addChild($elementName); // Adjust tag name as needed
                                foreach ($item as $subKey => $subValue) {
                                    $itemElement->addChild($subKey, htmlspecialchars((string)$subValue));
                                }
                            } else {
                                // Handle simple arrays (e.g., photoUrls)
                                $child->addChild($elementName, htmlspecialchars((string)$item));
                            }
                        }
                    } else {
                        // Handle nested object fields (e.g., category)
                        foreach ($value as $subKey => $subValue) {
                            $child->addChild($subKey, htmlspecialchars((string)$subValue));
                        }
                    }
                } else {
                    // Update scalar fields
                    $petElement->{$key} = htmlspecialchars((string)$value);
                }
            }
        }
    }
}
