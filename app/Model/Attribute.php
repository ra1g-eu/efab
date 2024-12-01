<?php
namespace App\Model;

class Attribute
{
    private int $id;
    private string $name;
    private string $type;
    private bool $isRequired;

    public function __construct(int $id, string $name, string $type, bool $isRequired)
    {
        $this->id = $id;
        $this->name = $name;
        $this->type = $type;
        $this->isRequired = $isRequired;
    }

    // Getter na ID atribútu
    public function getId(): int
    {
        return $this->id;
    }

    // Getter na názov atribútu
    public function getName(): string
    {
        return $this->name;
    }

    // Setter na názov atribútu
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    // Getter na typ atribútu
    public function getType(): string
    {
        return $this->type;
    }

    // Setter na typ atribútu
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    // Getter na povinnosť atribútu
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    // Setter na povinnosť atribútu
    public function setRequired(bool $isRequired): void
    {
        $this->isRequired = $isRequired;
    }

    // Konverzia atribútu do asociatívneho poľa
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'is_required' => $this->isRequired,
        ];
    }
}
