<?php

namespace Cee\SimpleDi;

class Container {
  public function createServiceOnce($name) {
    if (key_exists($name, $this->_usedServices)) {
      throw new Exception("Service '$name' can be created only once");
    }
    $this->_usedServices[$name] = true;

    return $this->_createService($name);
  }

  public function setInterfaceImplementation($interfaceName, $implementationName) {
    if (key_exists($interfaceName, $this->_implementations)) {
      throw new Exception("Implementation of interface '$interfaceName' is already defined");
    }
    $this->_implementations[$interfaceName] = $implementationName;
  }

  protected function addServiceInstance($instance) {
    $name = get_class($instance);
    if ($this->isCreated($name)) {
      throw new Exception("Service '$name' is already created");
    }
    $this->_createdServices[$name] = $instance;
  }

  protected function getService($name) {
    return $this->_createService($name);
  }

  private $_createdServices = [];
  private $_usedServices = [];
  private $_implementations = [];

  private function _createService($name) {
    if (!$this->isCreated($name)) {
      if (!class_exists($name) && !interface_exists($name)) {
        throw new Exception("Class '$name' is not defined");
      }

      $reflection = new \ReflectionClass($name);

      if ($reflection->isInterface()) {
        if (!key_exists($name, $this->_implementations)) {
          throw new Exception("Implementation of interface '$name' is not defined");
        }

        $reflection = new \ReflectionClass($this->_implementations[$name]);
      }

      $resolved = [];
      $params = is_null($reflection->getConstructor()) ? [] : $reflection->getConstructor()->getParameters();

      foreach ($params as $param) {
        if (is_null($param->getClass())) {
          throw new Exception("One of constructor parameter of class '$reflection->name' is not typehinted");
        }

        $resolved[] = $this->_createService($param->getClass()->name);
      }

      $this->_createdServices[$name] = $reflection->newInstanceArgs($resolved);
    }

    return $this->_createdServices[$name];
  }

  private function isCreated($name) {
    return key_exists($name, $this->_createdServices);
  }
}
