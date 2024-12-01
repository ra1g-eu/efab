## Vytvorenie noveho atributu

`App\Model\Pet.php`

1. vytvorit novu konstantu `const ATTRIBUTES = "atributy"`
2. vlozit ju do `$fillable`
3. ak atribut bude pole 
   1. vytvorit novu konstantu podla singularu slova `const ATTRIBUTE = "atribut"`
   2. vlozit konstantu `ATTRIBUTES` do `ARRAY_FIELDS`
   3. vlozit konstantu `ATTRIBUTE` do `SINGULAR_ELEMENTS` v tvare `ATTRIBUTES => ATTRIBUTE`
   4. vytvorit typed property s nazvom atributu `public array $attributes = []`
   5. v konstruktore triedy Pet, do `match()` funkcie pre array elementy doplnit `self::ATTRIBUTES`

    
## API volania

### POST /api/pet/create
vytvorenie zvieratka

```json
{
  "name": "Peso1",
  "category": {
    "id": 1,
    "name": "Dogs"
  },
  "age": 5,
  "owners": [
    {
        "id": 0,
        "name": "Jozko Mrkvicka"
    }
  ],
  "photoUrls": [
    "eeeeee.png"
  ],
  "tags": [
    {
      "id": 0,
      "name": "corgi"
    }
  ],
  "status": "unavailable"
}
```

### PUT /api/pet/update
editovanie zvieratka, vstup rovnaky, pokial bude element chybat, tak sa vymaze

```json
{
  "name": "Peso1",
  "category": {
    "id": 1,
    "name": "Dogs"
  },
  "age": 5,
  "owners": [
    {
        "id": 0,
        "name": "Jozko Mrkvicka"
    }
  ],
  "photoUrls": [
    "eeeeee.png"
  ],
  "tags": [
    {
      "id": 0,
      "name": "corgi"
    }
  ],
  "status": "unavailable"
}
```

### GET /api/pet/[id]
vyhladanie zvieratka, treba dodat hladane ID

### DELETE /api/pet/delete/[id]
vymazanie zvieratka, treba dodat ID na zmazanie