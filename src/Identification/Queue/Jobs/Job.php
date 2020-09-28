<?php

namespace Tenancy\Identification\Drivers\Queue\Jobs;

use ReflectionClass;

class Job
{
    protected $tenant;

    protected $tenant_identifier;

    protected $tenant_key;

    public function getTenant()
    {
        return $this->tenant;
    }

    public function getTenantIdentifier()
    {
        return $this->tenant_identifier;
    }

    public function getTenantKey()
    {
        return $this->tenant_key;
    }

    public function __unserialize(array $values)
    {
        $properties = (new ReflectionClass($this))->getProperties();

        $class = get_class($this);

        foreach($properties as $property) {
            if(!in_array($property->getName(), ['tenant', 'tenant_identifier', 'tenant_key'])){
                continue;
            }

            $name = "\0*\0{$property->getName()}";

            if (! array_key_exists($name, $values)) {
                continue;
            }

            $property->setAccessible(true);

            $property->setValue($this, unserialize(serialize($values[$name])));
        }
    }
}