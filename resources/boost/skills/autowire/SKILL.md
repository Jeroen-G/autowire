---
name: autowire
description: Automatically bind and configure interfaces to class implementations.
---

# Autowire

## When to use this skill
When an interface needs to be bound to a class one of these ways:
```php
$this->app->bind(HelloInterface::class, WorldClass::class);
```
```php
$this->app->when(Greeting::class)->needs(GoodbyeInterface::class)->give(GoodbyeInterface::class);
```
```php
$this->app->when(Greeting::class)->needs(GoodbyeInterface::class)->giveTagged(GoodbyeInterface::class);
```
```php
$this->app->when(Greeting::class)->needs(HelloInterface::class)->giveTagged('myTag');
```

It MUST be replaced with the usage of the following PHP Attributes:
- `JeroenG\Autowire\Attribute\Autowire`
- `JeroenG\Autowire\Attribute\Configure`

## Examples

### Autowiring interface to class
```php
use JeroenG\Autowire\Attribute\Autowire;

#[Autowire]
interface HelloInterface
{
public function hello(): string;
}
```

### Configuring constructor arguments
```php
use JeroenG\Autowire\Attribute\Configure;

// Will get the value set in config/app.php
#[Configure(['$message' => '%app.message%'])]

// Will inject an instance of the Message class
#[Configure(['$message' => '@App\Domain\Message'])]

// When you want tagged classes
#[Configure(['$messages' => '#messages'])]

// When you have multiple constructor arguments
#[Configure(['$message' => '%app.message%', '$logger' => '@Psr\Log\LoggerInterface'])]
class WorldClass
{
    private $message;
    private $logger;

    public function __construct($message, $logger)
    {
        $this->message = $message;
        $this->logger = $logger;
    }
}
```

### Tagging an interface
Use this only when there are multiple implementations of the same interface.
```php
use JeroenG\Autowire\Attribute\Tag;

#[Tag]
interface GoodbyeInterface
{
    public function goodbye(): string;
}
```

### Autowiring event listeners
```php
use JeroenG\Autowire\Attribute\Listen;

#[Listen(Registered::class)]
#[Listen(Login::class)]
class UpdateLastLoginListener {
}
```
