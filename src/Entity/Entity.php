<?php

namespace Polustrovo\Entity;

trait Entity
{
    /** @var array */
    private $changes = [];

    /**
     * @return array
     */
    public function changes(): array
    {
        return $this->changes;
    }

    public static function create(array $data = [])
    {
        $obj = new static();

        foreach ($data as $propertyName => $value) {
            if (property_exists($obj, $propertyName)) {
                $obj->set($propertyName, $value);
            }
        }

        return $obj;
    }

    /**
     * @param array $args
     * @return static
     */
    public function with(array $args)
    {
        $clonedClass = clone $this;

        foreach($args as $propertyName => $value) {
            if (property_exists($clonedClass, $propertyName)) {
                $clonedClass->set($propertyName, $value);
            }
        }

        $changes = array_fill_keys($clonedClass->changes, true);
        $changes += array_fill_keys(array_keys($args), true);

        $clonedClass->set('changes', array_keys($changes));

        return $clonedClass;
    }

    /**
     * @param string $propertyName
     * @param $value
     */
    private function set(string $propertyName, $value)
    {
        if (in_array($propertyName, static::DATES, true)) {
            $value = new \DateTimeImmutable($value, new \DateTimeZone('UTC'));
        }

        if (array_key_exists($propertyName, static::IDS)) {
            $idClass = static::IDS[$propertyName];

            $value = new $idClass($value);
        }

        $this->{$propertyName} = $value;
    }
}