# SimpleDi
Simple Dependency Injection Container

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
class ErrorLogLogger {
  public function log($message) {
    error_log('Error: ' . $message);
  }
}
```

```php
class SendMailMailer {
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

