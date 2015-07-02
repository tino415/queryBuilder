<?php

namespace tino\queryBuilder;

trait ConfigurationTrait {

    function loadOptions($options)
    {
        foreach($options as $option_name => $option_value)
        {
            $this->{$option_name} = $option_value;
        }
    }

    function instantce($classWithConfig)
    {
        if (is_array($classWithConfig) || $classWithConfig instanceof ArrayAccess) {
            $className = $classWithConfig['class'];
            unset($classWithConfig['class']);
            return new $className($classWithConfig);
        } 

        if (is_string($classWithConfig)) {
            return new $classWithConfig;
        }

        return $classWithConfig;
    }

}
