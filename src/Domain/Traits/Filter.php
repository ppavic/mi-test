<?php

namespace App\Domain\Traits;

use DateTime;

trait Filter
{

    // filter attributes and corresponding jsonl mappings
    protected array $defaultAattributes = [
        'city' => 'Aaddress->City',
        'pets' => 'Aaddress->Pets',
        'children' => 'Aaddress->Kids',
        'age_min' => 'Bbirthday',
        'age_max' => 'Bbirthday',
    ];

    protected array $defaultLogic;
    protected array $additionalAttributes;
    protected array $allAttributes;

    /**
     * Function that should be used inside __construct() of Class that uses this Trati.
     */

    public function InitializeLogic(): void
    {

        $this->defaultLogic = [
            //equal
            'eq' => function ($val1, $val2) {return $val1 == $val2;},
            //greater than
            'gt' => function ($val1, $val2) {return $val1 > $val2;},
            //less than
            'lt' => function ($val1, $val2) {return $val1 < $val2;},
            //greater than or equal, inclusive
            'gte' => function ($val1, $val2) {return $val1 >= $val2;},
            //less than or equal, inclusive
            'lte' => function ($val1, $val2) {return $val1 <= $val2;},
            // itd...
        ];

        // additional "user" attributes / overwrites keys of default logic
        $this->additionalAttributes = [];

        // merge of default and additional attributes
        $this->allAttributes = array_merge($this->defaultAattributes, $this->additionalAttributes);

    }

    /**
     * Function for filtering JSON object by condition
     *
     * Undocumented function long description
     *
     * @param array $data Data that will be
     * @param array $criteria List of criteria for filtering
     * @param bool $allCriteria Controls if all criteria is contraint. For any of criteria value should be set to false
     * @return array Of filtered objects
     **/
    public function filter(array $data, array $criteria, bool $allCriteria = true)
    {
        /** @var array $result Results of filtering */
        $result = [];

        /** If there is no criteria complete dataset will be returned */
        if (empty($criteria)) {
            return $data;
        }

        /** Proccesing of each $data element */
        foreach ($data as $object) {

            /**
             *  Check if any criteria exist in all attributes
             *  Atleast one one criteria has to exist.
             *  If criteria does not exist filtering stops,
             *  all data is returned.
             */

            foreach ($criteria as $filterAttrib => $condition) {
                if ($filterAttrib == null || $filterAttrib == "") {
                    return $data;
                }

                if (!key_exists($condition['logic'], $this->defaultLogic)) {
                    return $data;
                }
            }

            // following all criteria
            if ($allCriteria) {
                $match = true;

                foreach ($criteria as $key => $value) {

                    //will hold actual value of object property
                    $objectValue = $object;

                    // get parts to access value of object
                    $parts = explode('->', $this->allAttributes[$key]);

                    //get value
                    foreach ($parts as $prop) {
                        $objectValue = $objectValue->{$prop};
                    }

                    //process logic which is not minimum or maximum age
                    if ($key != 'age_min' && $key != 'age_max') {

                        $match = $match && $this->defaultLogic[$value['logic']]($objectValue, $value['value']);

                    }
                    //process logic for minimum or maximum age
                    if ($key == 'age_min') {

                        $age = $this->yearsDifference($object->Bbirthday);
                        $match = $match && $this->defaultLogic[$value['logic']]($age, intval($value['value']));
                    }
                    if ($key == 'age_max') {

                        $age = $this->yearsDifference($object->Bbirthday);
                        $match = $match && $this->defaultLogic[$value['logic']]($age, intval($value['value']));

                    }

                }

                // for object to be stored as result $match has to be true
                if ($match) {
                    $result[] = $object;
                }
            }

            // following any criteria
            if (!$allCriteria) {
                $match = false;

                foreach ($criteria as $key => $value) {
                    $parts = explode('->', $this->allAttributes[$key]);
                    $objectValue = $object;

                    foreach ($parts as $prop) {
                        $objectValue = $objectValue->{$prop};
                    }

                    if ($key != 'age_min' && $key != 'age_max') {

                        $match = $match || $this->defaultLogic[$value['logic']]($objectValue, $value['value']);

                    }

                    if ($key == 'age_min') {

                        $age = $this->yearsDifference($object->Bbirthday);
                        $match = $match || $this->defaultLogic[$value['logic']]($age, intval($value['value']));

                    }

                    if ($key == 'age_max') {

                        $age = $this->yearsDifference($object->Bbirthday);
                        $match = $match || $this->defaultLogic[$value['logic']]($age, intval($value['value']));

                    }
                }
                if ($match) {
                    $result[] = $object;
                }
            }

        }

        return $result;

    }
    /**
     * Function which calculates year difference between given date and today.
     * @param string $date Date for which we want to find difference in years. format: yyyy-mm-dd
     * @return int Number of years
     */

    public function yearsDifference(string $date)
    {
        $date = new DateTime($date);

        $today = new DateTime('today');

        return date_diff($date, $today)->y;
    }
}
