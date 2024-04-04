<?php

namespace App\Domain\Helpers;

use App\Domain\Helpers\Database;
use App\Domain\Helpers\FieldMap;
use App\Domain\Traits\Filter;
use App\Domain\Traits\JSONLFileReader;

use Exception;

class Seeder extends Database
{

    use Filter;
    use JSONLFileReader;

    private const FILE_NAME = "var/input.jsonl";

    private FieldMap $map;

    // table columns that can be filled with data
    protected $fillable = [];

    public function __construct()
    {

        // initialize filter logic
        $this->InitializeLogic();

        //initialize FiledMap
        $this->map = new FieldMap();

        //initialize fields in table that can be filled
        $this->fillable = [
            'Name',
            'Surname',
            'DOB',
            'Street',
            'Kids',
            'Pets',
            'City',
            'Zip',
            'Country',
        ];

        // initialize parent class
        parent::__construct();
    }

    /**
     * Function that fills table in database
     *
     * @param array $criteria Criteria for filtering data [ 'children' => ['value' => 0, 'logic' => 'gt'], ...]
     * @param string $option
     * @param boolean $rollback
     * @return void
     */
    public function seed(array $criteria, string $option = "all", bool $rollback = false): void
    {

        $fillData = [];

        //check if file exists.
        if (!file_exists($this::FILE_NAME)) {
            throw new Exception("Seeder class");
            return;
        }

        // map fields
        $this->map->addMap("Nname", "Name");
        $this->map->addMap("Surname", "Family Name");
        $this->map->addMap("Bbirthday", "DOB");

        //get data from file
        $data = $this->readJSONLFile($this::FILE_NAME);

        if (isset($data) && !empty($data) && $option == 'all') {

            //get filtered results
            $filteredData = $this->filter($data, $criteria);

            foreach ($filteredData as $object) {

                foreach ($this->fillable as $columnName) {

                    if ($this->map->getSource($columnName) != null) {

                        $fillData[$columnName] = $this->getObjectvalue($object, $this->map->getSource($columnName));

                    } else {

                        $fillData[$columnName] = $this->getObjectvalue($object, $columnName);
                    }
                }

                $this->insertRow('data', $fillData);

            }

        }

        if (isset($data) && !empty($data) && $option == 'any') {

            //get filtered results
            $filteredData = $this->filter($data, $criteria, false);

            foreach ($filteredData as $object) {

                foreach ($this->fillable as $columnName) {

                    if ($this->map->getSource($columnName) != null) {
                        $fillData[$columnName] = $this->getObjectvalue($object, $this->map->getSource($columnName));
                    } else {
                        $fillData[$columnName] = $this->getObjectvalue($object, $columnName);
                    }
                }

                $this->insertRow('data', $fillData);

            }

        }

    }

    private function getObjectValue($data, $columnName): mixed
    {

        foreach ($data as $key => $value) {
            
            if (is_object($value)) {
            
                return $this->getObjectValue($value, $columnName);
            } else {
            
                if ($key == $columnName) {
                    return $value;
                }
            }
        }
        return null;
    }
}
