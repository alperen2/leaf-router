<?php

namespace App;

use ReflectionClass;

class ExampleContainer {
    /**
     * @param string[] handler 
     * @param array[] params
     */
    public function call($handler, $params) {
        [$controller, $method] = $handler;
        $reflectionClass = new ReflectionClass($controller);
        $staticMethods = $reflectionClass->getMethods();

        if (in_array($method, $staticMethods)) {
            forward_static_call_array([$controller, $method], $params);
        } else {
            call_user_func_array([new $controller(), $method], $params);
        }
    }
}