<?php

namespace Core;

/**
 * Base model for all entities.
 *
 * @category Model
 * @package  Core
 * @author   Dinesh Radjou <dinesh.radjou@etu.univ-amu.fr>
 * @license  MIT License https://opensource.org/licenses/MIT
 * @link     https://github.com/RADJOU-Dinesh-24003262/SAEManager
 */
abstract class BaseModel
{
    /**
     * Hydrates the object with the provided data.
     *
     * @param array<string, mixed> $data Data to hydrate with.
     * @return void
     */
    protected function hydrate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Converts the object to an associative array
     * Uses reflection to be generic
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $reflection = new \ReflectionClass($this);
        $properties = $reflection->getProperties(
            \ReflectionProperty::IS_PRIVATE | \ReflectionProperty::IS_PROTECTED
        );

        $data = [];
        foreach ($properties as $property) {
            $property->setAccessible(true);
            $name = $property->getName();
            $data[$name] = $property->getValue($this);
        }

        return $data;
    }

    /**
     * Abstract method for model-specific validation
     *
     * @return array<int, string> Array of errors (empty if valid)
     */
    abstract public function validate(): array;
}
