<?php

namespace Cee\SimpleDi\Tests\InterfaceImplementation;

use Cee\SimpleDi;

require_once __DIR__ . '/../../Container.php';

class ClassA {}

class ClassB {
  private $class;

  public function __construct(InterfaceA $class) {
    $this->class = $class;
  }

  public function getClass() {
    return $this->class;
  }
}

interface InterfaceA {}

class ImplementationA implements InterfaceA {
  private $classA;

  public function __construct(ClassA $classA) {
    $this->classA = $classA;
  }

  public function getClassA() {
    return $this->classA;
  }
}

class SimpleContainer extends SimpleDi\Container {
  public function __construct() {
    $this->setInterfaceImplementation(InterfaceA::class, ImplementationA::class);
  }
}

class ContainerInterfaceTest extends \PHPUnit_Framework_TestCase {
  public function testInterfaceInjection() {
    $container = new SimpleContainer();
    $classB = $container->createServiceOnce(ClassB::class);

    $this->assertSame(ClassB::class, get_class($classB));
    $this->assertSame(ImplementationA::class, get_class($classB->getClass()));
  }
}

