<?php

namespace App;

class SchoolRepository
{
    const FILE = __DIR__ . '/../db/db.txt';

    public function save(array $data)
    {
        $data = json_encode($data, JSON_PRETTY_PRINT);
        file_put_contents(self::FILE, $data);
    }
    public function read($file = self::FILE)
    {
        $data = file_get_contents($file);
        return json_decode($data, true);
    }
    public function find($data) // this function does not work right it only demonstrates working of update
    {
        $data = file_get_contents(self::FILE);
        return json_decode($data, true);
    }

}
