<?php

namespace App\Domain\Helpers;

trait FieldMap
{
    private array $fieldMap;

    public function __construct()
    {
        // init empty array
        $this->fieldMap = array();
    }

    /**
     * Adds mapping
     *
     * @param string $sourceField Source field that will be mapped to destination field
     * @param string $destinationField
     * @return void
     */
    public function addMap(string $sourceField, string $destinationField): void
    {
        $this->fieldMap[$sourceField] = $destinationField;
    }

    /**
     * Gets value of destination field based on source feield value
     *
     * @param string $sourceField 
     * @return string|null
     */
    public function getDestination(string $sourceField): ?string
    {
        return isset($this->fieldMap[$sourceField]) ? $this->fieldMap[$sourceField] : null;
    }

    /**
     * Gets destination field based on value of sourcefield
     *
     * @param string $destinationField
     * @return string|null
     */
    public function getSource(string $destinationField): ?string
    {
        return array_search($destinationField, $this->fieldMap) ? array_search($destinationField, $this->fieldMap) : null;
    }
    /**
     * clears all mappings
     *
     * @return void
     */
    public function clearMapping(): void
    {
        //reset to empty array
        $this->fieldMap = array();
    }
}
