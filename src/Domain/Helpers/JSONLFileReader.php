<?php

namespace App\Domain\Helpers;

use SplFileObject;

trait JSONLFileReader
{
    /**
     * Function that reads prvide JSONL file.
     *
     * Function reads file and returns array of objects (not assocciative array).
     * 
     * @var string $filename path to .jsonl.
     * @return type array result is list of objects.
     **/
    private function readJSONLFile(string $filename): array
    {

        $result = [];

        $file = new SplFileObject($filename, 'r');

        while (!$file->eof()) {
            // read line
            $line = $file->fgets();
            // get object data for line
            if ($line != null || $line != "" || trim($line) != "") {
                $data = json_decode($line);
            }
            // append to result variable which will be return value
            $result[] = $data;
        }

        //close file:
        $file = null;

        return $result;

    }

}
