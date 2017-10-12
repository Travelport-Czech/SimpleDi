# SimpleDi
Simple Dependency Injection Container

[![Build Status](https://travis-ci.org/Travelport-Czech/SimpleDi.svg?branch=master)](https://travis-ci.org/Travelport-Czech/SimpleDi)

Useful for making dependency tree of a service classes. The Service class is a class which has only one instance in a application. It can be used for simple alternative to Nette Di Container. All configuration of the DI Container is in PHP language.

## Basic usage

```php
$container = new Cee\SimpleDi\Container();
$classInstance = $container->createServiceOnce('ClassName');
```

## Example with interfaces as the dependencies

```php
interface Logger {
  public function log($message);
}
```

```php
interface Mailer {
  public function send($message, $subject, $to);
}
```
The Service class providing some functionality with dependencies on previous interfaces:
```php
class NotificationService {
  private $mailer;
  private $logger;

  public function __construct(Mailer $mailer, Logger $logger) {
    $this->mailer = mailer;
    $this->logger = logger;
  }

  public function notify($message, array $recipients) {
    $subject = 'Notification';
    foreach ($recipients as $recipient) {
      $this->mailer->send($message, $subject, $recipient);
    }
    $this->logger->log(...);
  }
}
```
Implementations of an interfaces:
```php
class ErrorLogLogger extends Logger {
  public function log($message) {
    error_log('Error: ' . $message);
  }
}
```

```php
class SendMailMailer extends Mailer {
  public function send($message, $subject, $to) {
    mail($to, $subject, $message);
  }
}
```
And finally the configuration of the Simple DI Container with created instance of the `NotificationService`:
```php
$container = new Cee\SimpleDi\Container();

$container->setInterfaceImplementation(Logger::class, ErrorLogLogger::class);
$container->setInterfaceImplementation(Mailer::class, SendMailMailer::class);

$notificationService = $container->createServiceOnce(NotificationService::class);
```
Simple Di Container fill in parameters by type hint (using PHP Reflection). This is called autowiring.

## Example with no type hinted parameter in constructor of the service class
```php
class NotificationService {
  private $mailer;
  private $logFileName;

  public function __construct(Mailer $mailer, $logFileName) {
    $this->mailer = mailer;
    $this->logFileName = logFileName;
  }

  public function notify($message, array $recipients) {
    $subject = 'Notification';
    foreach ($recipients as $recipient) {
      $this->mailer->send($message, $subject, $recipient);
    }
    file_put_contents($logFileName, ...);
  }
}
```
Container configuration created as extending class of the Simple DI Container:
```php
namespace App;

class Container extends \Cee\SimpleDi\Container {
  public function __construct($logFileName) {
    $notificationService = new \NotificationService(new SendMailMailer(), $logFileName);
    $this->addServiceInstance($notificationService);
  }
}
```
And in application we are using own Container:
```php
$logFileName = 'log.txt';
$container = new App\Container($logFileName);
$notificationService = $container->createServiceOnce(NotificationService::class);
```
This example has disadvantage - `NotificationService` is created at start of the application and not on demand as other service classes created by Simple DI Container. This is useful on old code without need refactoring code. But the goal of the refactoring is create wrapper on all no type hinted parameters of the service class. In this case first refactoring step is:
```php
class NotificationService {
  private $mailer;
  private $logFileName;

  public function __construct(Mailer $mailer, LogFileName $logFileName) {
  ...
```
After this, you do not need create NotificationService by own. Next step is creating of the service class Logger as is used in example with interface.

## Example with already created classes
Typical use case is an old smell code with singletons. Or if you need successive refactoring. You have already instance of the service class before you can initiate a DI Container. You need use autowiring of this class to other.
```php
class Repository {
  public function __construct(MySqlConnector $mySqlConnector) {
  ...
}
```

```php
class Container extends \Cee\SimpleDi\Container {
  public function __construct(MySqlConnector $mySqlConnector) {
    $this->addServiceInstance($mySqlConnector);
  }
}
```
And it works:
```php
$mySqlConnector = MySqlConnector::instance();
$container = new App\Container($mySqlConnector);
$repository = $container->createServiceOnce(Repository::class);
```
