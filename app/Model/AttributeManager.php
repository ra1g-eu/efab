<?php

namespace App\Model;

use Nette\Database\Explorer;

class AttributeManager
{
    private Explorer $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function getAllAttributes(): array
    {
        $result = $this->db->table('attributes')->fetchAll();

        $attributes = [];

        foreach ($result as $row) {
            $attributes[] = new Attribute(
                $row->id,
                $row->name,
                $row->type,
                (bool)$row->is_required
            );
        }

        return $attributes;
    }

    public function createAttribute(string $name, string $type, bool $isRequired): void
    {
        $this->db->table('attributes')->insert([
            'name' => $name,
            'type' => $type,
            'is_required' => $isRequired,
        ]);
    }

    public function deleteAttribute(int $id): void
    {
        $this->db->table('attributes')->where('id', $id)->delete();
    }

    public function createTables(): void
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS attributes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                type TEXT NOT NULL,
                is_required INTEGER NOT NULL DEFAULT 0
            )
        ");

        $this->db->query("
            CREATE TABLE IF NOT EXISTS pets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                path TEXT NOT NULL
            )
        ");
    }
}
