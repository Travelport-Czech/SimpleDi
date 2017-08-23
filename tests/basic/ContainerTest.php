<?php

namespace Cee\SimpleDi\Tests\Basic;

use Cee\SimpleDi;

require_once __DIR__ . '/../../Container.php';

class ClassA {}

class ClassB {
  private $classA;

  public function __construct(ClassA $classA) {
    $this->classA = $classA;
  }

  public function getClassA() {
    return $this->classA;
  }
}

class ClassC {
  public function __construct($notTypehintedParameter) {}
}

class SimpleContainer extends SimpleDi\Container {}

class ContainerBasicTest extends \PHPUnit_Framework_TestCase {
  public function testWithOneClass() {
    $container = new SimpleContainer();
    $classA = $container->createServiceOnce(ClassA::class);

    $this->assertSame(ClassA::class, get_class($classA));
  }

  public function testWithOneClassAndOneDependency() {
    $container = new SimpleContainer();
    $classB = $container->createServiceOnce(ClassB::class);

    $this->assertSame(ClassB::class, get_class($classB));
    $this->assertSame(ClassA::class, get_class($classB->getClassA()));
  }

  public function testUnknownClass() {
    $this->expectException(SimpleDi\Exception::class);
    $this->expectExceptionMessage("Class 'UnknownClass' is not defined");

    $container = new SimpleContainer();
    $container->createServiceOnce('UnknownClass');
  }

  public function testCreateServiceTwice() {
    $this->expectException(SimpleDi\Exception::class);
    $this->expectExceptionMessage("Service 'Cee\SimpleDi\Tests\Basic\ClassA' can be created only once");

    $container = new SimpleContainer();
    $container->createServiceOnce(ClassA::class);
    $container->createServiceOnce(ClassA::class);
  }

  public function testNotTypehintedParameter() {
    $this->expectException(SimpleDi\Exception::class);
    $this->expectExceptionMessage("One of constructor parameter of class 'Cee\SimpleDi\Tests\Basic\ClassC' is not typehinted");

    $container = new SimpleContainer();
    $container->createServiceOnce(ClassC::class);
  }
}

