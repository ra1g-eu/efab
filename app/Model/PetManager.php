<?php

namespace App\Model;

use Exception;
use Nette\Database\Explorer;
use SimpleXMLElement;

class PetManager
{
    private Explorer $database;
    private string $xmlDirectory;

    public function __construct(Explorer $database, string $xmlDirectory)
    {
        $this->database = $database;
        $this->xmlDirectory = $xmlDirectory;
    }

    public function createPet(array $data): int
    {
        bdump($data, 'Creating Pet');

        $this->database->table('pets')->insert(['path' => '']);

        $id = $this->database->getInsertId();

        $fileName = "pet_$id.xml";
        $filePath = $this->xmlDirectory . "/" . $fileName;

        $xml = new SimpleXMLElement('<pet/>');
        foreach ($data as $key => $value) {
            $xml->addChild($key, $value);
        }
        $xml->asXML($filePath);

        $this->database->table('pets')->where('id', $id)->update(['path' => $fileName]);

        return $id;
    }

    // Aktualizácia zvieratka

    /**
     * @throws Exception
     */
    public function updatePet(int $id, array $data): void
    {
        $pet = $this->database->table('pets')->get($id);
        if (!$pet) {
            throw new Exception('Pet not found.');
        }

        $filePath = $pet->path;
        $xml = simplexml_load_file($filePath);

        foreach ($data as $key => $value) {
            $xml->$key = $value;
        }

        $xml->asXML($filePath);
    }

    // Vymazanie zvieratka

    /**
     * @throws Exception
     */
    public function deletePet(int $id): void
    {
        $pet = $this->database->table('pets')->get($id);
        if (!$pet) {
            throw new Exception('Pet not found.');
        }

        $filePath = $pet->file_path;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->database->table('pets')->where('id', $id)->delete();
    }

    // Načítanie všetkých zvierat
    public function getAllPets(): array
    {
        $pets = $this->database->table('pets')->fetchAll();
        $result = [];

        foreach ($pets as $pet) {
            $filePath = $this->xmlDirectory . "\\" . $pet->path;
            bdump($filePath, 'File Path');
            bdump($pet->path, 'Pet Path');
            if (file_exists($filePath)) {
                $xml = simplexml_load_file($filePath);
                $result[] = [
                    'id' => $pet->id,
                    'data' => json_decode(json_encode($xml), true),
                ];
            }
        }

        bdump($result, 'All Pets');

        return array_values($result);
    }


    /**
     * @param int $id
     * @return array|null
     */
    public function getPetById(int $id): ?array
    {
        // Získame cestu k XML súboru z databázy
        $row = $this->database->table('pets')->get($id);

        if (!$row) {
            return null; // Zvieratko s týmto ID neexistuje
        }

        $filePath = $this->xmlDirectory . '/' . $row->path;

        if (!file_exists($filePath)) {
            return null; // XML súbor neexistuje
        }

        // Načítame XML súbor
        $xmlContent = simplexml_load_file($filePath);

        if ($xmlContent === false) {
            return null; // Chyba pri načítaní XML
        }

        // Konvertujeme XML na pole
        $petData = [];
        foreach ($xmlContent->children() as $key => $value) {
            $petData[$key] = (string)$value;
        }

        // Vraciame dáta zvieratka spolu s jeho ID
        return [
            'id' => $id,
            'data' => $petData,
        ];
    }
}
