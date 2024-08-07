# App Spam

Spam protection for forms and detecting spam using the validator.

## Table of Contents

- [Getting Started](#getting-started)
    - [Requirements](#requirements)
- [Documentation](#documentation)
    - [App](#app)
    - [Spam Boot](#spam-boot)
        - [Spam Config](#spam-config)
    - [Basic Usage](#basic-usage)
        - [Render Detector](#render-detector)
        - [Detect Spam](#detect-spam)
    - [Available Detectors](#available-detectors)
        - [Composite Detector](#composite-detector)
        - [EmailDomain Detector](#emaildomain-detector)
        - [EmailRemote Detector](#emailremote-detector)
        - [Honeypot Detector](#honeypot-detector)
        - [MinTimePassed Detector](#mintimepassed-detector)
        - [Null Detector](#null-detector)
        - [WithoutUrl Detector](#withouturl-detector)
    - [Available Factories](#available-factories)
        - [Composite Factory](#composite-factory)
        - [EmailRemote Factory](#emailremote-factory)
        - [Honeypot Factory](#honeypot-factory)
        - [MinTimePassed Factory](#mintimepassed-factory)
        - [Named Factory](#named-factory)
        - [WithoutUrl Factory](#withouturl-factory)
    - [Register Named Detectors](#register-named-detectors)
    - [Manually Detecting Spam](#manually-detecting-spam)
    - [Detecting Spam Using Validator](#detecting-spam-using-validator)
    - [Http Spam Error Handler Boot](#http-spam-error-handler-boot)
    - [Events](#events)
- [Credits](#credits)
___

# Getting Started

Add the latest version of the app spam project running this command.

```
composer require tobento/app-spam
```

## Requirements

- PHP 8.0 or greater

# Documentation

## App

Check out the [**App Skeleton**](https://github.com/tobento-ch/app-skeleton) if you are using the skeleton.

You may also check out the [**App**](https://github.com/tobento-ch/app) to learn more about the app in general.

## Spam Boot

The spam boot does the following:

* installs and loads spam config file
* implements spam interfaces

```php
use Tobento\App\AppFactory;
use Tobento\App\Spam\DetectorsInterface;

// Create the app
$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Spam\Boot\Spam::class);
$app->booting();

// Implemented interfaces:
$detectors = $app->get(DetectorsInterface::class);

// Run the app
$app->run();
```

### Spam Config

The configuration for the spam is located in the ```app/config/spam.php``` file at the default App Skeleton config location where you can specify detectors for your application.

## Basic Usage

### Render Detector

In your view file, render the detector on your form using the ```spamDetector``` view macro:

```php
<form>
    <!-- Using the default -->
    <?= $view->spamDetector()->render($view) ?>
    
    <!-- Or using a specific detector -->
    <?= $view->spamDetector('register')->render($view) ?>
</form>
```

Check out the [App View](https://github.com/tobento-ch/app-view) to learn more about it.

**Using a factory**

Alternatively, you can specify a detector factory:

```php
use Tobento\App\Spam\Factory;

<form>
    <?= $view->spamDetector(new Factory\Honeypot(inputName: 'name'))->render($view) ?>
</form>
```

Check out the [Available Factories](#available-factories) for its available detector factories.

### Detect Spam

To protect your form against spam, add the ```ProtectAgainstSpam``` middleware to the route that your form points to.

```php
use Tobento\App\AppFactory;
use Tobento\App\Spam\Middleware\ProtectAgainstSpam;

$app = (new AppFactory())->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\View\Boot\View::class);
$app->boot(\Tobento\App\View\Boot\Form::class);
$app->boot(\Tobento\App\Spam\Boot\Spam::class);
$app->booting();

// Routes:
$app->route('POST', 'register', function() {
    // being spam protected!
    return 'response';
})->middleware([
    ProtectAgainstSpam::class,
    
    // you may specify a specific detector other than the default:
    'detector' => 'register',
    
    // or you may specify a detector factory:
    'detector' => new Factory\Composite(
        new Factory\Named('default'),
        new Factory\WithoutUrl(inputNames: ['message']),
    ),
]);

// Run the app:
$app->run();
```

Check out the [Available Factories](#available-factories) for its available detector factories.

## Available Detectors

### Composite Detector

The ```Composite``` detector may be used to compose detectors:

```php
use Tobento\App\Spam\Detector;

$detector = new Detector\Composite(
    'default',
    new Detector\Honeypot(name: 'default', inputName: 'hp'),
);
```

### EmailDomain Detector

The ```EmailDomain``` detector, detects spam email domains from the the specified blacklist.

```php
use Tobento\App\Spam\Detector;

$detector = new Detector\EmailDomain(
    name: 'default',
    inputName: 'email',
    blacklist: ['mail.ru'], // Email domains considered as spam.
    whitelist: ['gmail.com'], // Email domains not considered as spam, exludes from blacklist.
);
```

You may consider to create a factory to import blacklisted email domains from a file or any other source.

### EmailRemote Detector

The ```EmailRemote``` detector, detects spam email domains using PHP In-built functions ```getmxrr()```, ```checkdnsrr()``` and ```fsockopen()``` to verify email domain.

```php
use Tobento\App\Spam\Detector;

$detector = new Detector\EmailRemote(
    name: 'default',
    inputName: 'email',
    checkDNS: true,
    checkSMTP: true,
    checkMX: true,
    timeoutInSeconds: 5,
);
```

### Honeypot Detector

The ```Honeypot``` detector, renders an invisible input element that should never contain a value when submitted. If a bot fills this input out, or removes the input from the request, the request will be detected as spam.

```php
use Tobento\App\Spam\Detector;

$detector = new Detector\Honeypot(
    name: 'default',
    inputName: 'hp',
);
```

### MinTimePassed Detector

The ```MinTimePassed``` detector, renders an invisible input element with the time in it as an encrypted value. If the form is submitted faster than defined ```milliseconds```, or removes the input from the request, the request will be detected as spam.

```php
use Psr\Clock\ClockInterface;
use Tobento\App\Spam\Detector;
use Tobento\Service\Encryption\EncrypterInterface;

$detector = new Detector\MinTimePassed(
    encrypter: $encrypter, // EncrypterInterface
    clock: $clock, // ClockInterface
    name: 'default',
    inputName: 'mtp',
    milliseconds: 1000,
);
```

You may check out the [App Encryption](https://github.com/tobento-ch/app-encryption) to learn more about it.

### Null Detector

The ```NullDetector``` detector does not detect any request as spam at all.

```php
use Tobento\App\Spam\Detector;

$detector = new Detector\NullDetector(name: 'null');
```

### WithoutUrl Detector

If the defined ```inputNames``` contain an URL, the request will be detected as spam by the ```WithoutUrl``` detector.

```php
use Tobento\App\Spam\Detector;

$detector = new Detector\WithoutUrl(
    name: 'default',
    inputNames: ['message'],
);
```

## Available Factories

### Composite Factory

The ```Composite``` factory creates a [Composite Detector](#composite-detector):

```php
use Tobento\App\Spam\Factory;

$factory = new Factory\Composite(
    new Factory\Honeypot(),
);
```

### EmailRemote Factory

The ```EmailRemote``` factory creates a [EmailRemote Detector](#emailremote-detector):

```php
use Tobento\App\Spam\Factory;

$factory = new Factory\EmailRemote(
    inputName: 'email',
    checkDNS: true,
    checkSMTP: true,
    checkMX: true,
    timeoutInSeconds: 5,
);
```

### Honeypot Factory

The ```Honeypot``` factory creates a [Honeypot Detector](#honeypot-detector):

```php
use Tobento\App\Spam\Factory;

$factory = new Factory\Honeypot(
    // you may change the default input name:
    inputName: 'hp',
);
```

### MinTimePassed Factory

The ```MinTimePassed``` factory creates a [MinTimePassed Detector](#mintimepassed-detector):

```php
use Tobento\App\Spam\Factory;

$factory = new Factory\MinTimePassed(
    // you may change the default input name:
    inputName: 'mtp',
    
    // you may change the default input name:
    milliseconds: 1000,
    
    // you may change the enrypter to be used, otherwise the default is used:
    encrypterName: 'spam',
);
```

### Named Factory

The ```Named``` factory may be used to create a detector from a named detector:

```php
use Tobento\App\Spam\Factory;

$factory = new Factory\Named(
    detector: 'default',
);
```

Check out the [Register Named Detectors](#register-named-detectors) section to learn more about it.

### WithoutUrl Factory

The ```WithoutUrl``` factory creates a [WithoutUrl Detector](#withouturl-detector):

```php
use Tobento\App\Spam\Factory;

$factory = new Factory\WithoutUrl(
    inputNames: ['message'],
);
```

## Register Named Detectors

**Register Named Detector via Config**

You can register named detectors in the config file ```app/config/spam.php```:

```php
use Tobento\App\Spam\Detector;
use Tobento\App\Spam\DetectorInterface;
use Tobento\App\Spam\Factory;

return [
    // ...
    'detectors' => [
        // using a factory:
        'default' => new Factory\Composite(
            new Factory\Honeypot(inputName: 'hp'),
            new Factory\MinTime(milliseconds: 1000),
        ),
        
        // using a closure:
        'secondary' => static function (string $name): DetectorInterface {
            return new Detector\Composite(
                new Detector\Honeypot(name: $name, inputName: 'hp'),
            );
        },
        
        // using a class instance:
        'null' => new Detector\NullDetector(name: 'null'),
    ],
];
```

**Register Named Detector via Boot**

```php
use Tobento\App\Boot;
use Tobento\App\Spam\Boot\Spam;
use Tobento\App\Spam\Detector;
use Tobento\App\Spam\DetectorFactoryInterface;
use Tobento\App\Spam\DetectorsInterface;
use Tobento\App\Spam\Factory;

class SpamDetectorsBoot extends Boot
{
    public const BOOT = [
        // you may ensure the spam boot.
        Spam::class,
    ];
    
    public function boot()
    {
        // you may use the app on method to add only if requested:
        $app->on(
            DetectorsInterface::class,
            static function(DetectorsInterface $detectors) {
                $detectors->add(
                    name: 'null',
                    detector: new Detector\NullDetector(), // DetectorInterface|DetectorFactoryInterface
                );
            }
        );
    }
}
```

## Manually Detecting Spam

After having [booted the spam](#spam-boot), inject the ```DetectorsInterface``` in any service or controller.

```php
use Psr\Http\Message\ServerRequestInterface;
use Tobento\App\Spam\DetectorsInterface;
use Tobento\App\Spam\Exception\SpamDetectedException;

class SpamService
{
    public function isSpam(ServerRequestInterface $request, DetectorsInterface $detectors): bool
    {
        try {
            $detectors->get('name')->detect($request);
        } catch (SpamDetectedException $e) {
            return true;
        }
        
        return false;
    }
}
```

**Detecting spam from value**

```php
use Psr\Http\Message\ServerRequestInterface;
use Tobento\App\Spam\DetectorsInterface;
use Tobento\App\Spam\Exception\SpamDetectedException;

class SpamService
{
    public function isSpam(mixed $value, DetectorsInterface $detectors): bool
    {
        try {
            $detectors->get('name')->detectFromValue($value);
        } catch (SpamDetectedException $e) {
            return true;
        }
        
        return false;
    }
}
```

## Detecting Spam Using Validator

**Requirements**

It requires the [App Validation](https://github.com/tobento-ch/app-validation):

```
composer require tobento/app-validation
```

In addition, you may boot the ```ValidationSpamRule``` boot if you want to support string definition rule like ```spam:detector_name```.

```php
use Tobento\App\AppFactory;

// Create the app
$app = (new AppFactory())->createApp();

// Adding boots
$app->boot(\Tobento\App\Spam\Boot\ValidationSpamRule::class);
$app->boot(\Tobento\App\Spam\Boot\Spam::class);

// Run the app
$app->run();
```

Otherwise, you will need to boot the validator boot:

```php
use Tobento\App\AppFactory;

// Create the app
$app = (new AppFactory())->createApp();

// Adding boots
$app->boot(\Tobento\App\Validation\Boot\Validator::class);
$app->boot(\Tobento\App\Spam\Boot\Spam::class);

// Run the app
$app->run();
```

**Spam Rule**

```php
use Tobento\App\Spam\Factory;
use Tobento\App\Spam\Validation\SpamRule;

$validation = $validator->validating(
    value: 'foo@example.com',
    rules: [
        // using a detector name:
        new SpamRule(
            detector: 'email',
            
            // you may specify a custom error message:
            errorMessage: 'Custom error message',
        ),
        
        // using a detector factory:
        new SpamRule(detector: new Factory\Named('email')),
        
        // or if booted the ValidationSpamRule::class:
        'spam:email',
        
        // or with multiple detector names:
        'spam:emailRemote:emailDomain',
    ],
);
```

**Skip validation**

You may use the skipValidation parameter in order to skip validation under certain conditions:

```php
$validation = $validator->validating(
    value: 'foo',
    rules: [
        // skips validation:
        new SpamRule(detector: 'email', skipValidation: true),
        
        // does not skip validation:
        new SpamRule(detector: 'email', skipValidation: false),
        
        // skips validation:
        new SpamRule(detector: 'email', skipValidation: fn (mixed $value): bool => $value === 'foo'),
    ],
);
```

## Http Spam Error Handler Boot

The http error handler boot does the following:

* handles ```Tobento\App\Spam\Exception\SpamDetectedException::class``` exceptions.

The boot is automatically loaded by the [Spam Boot](#spam-boot).

```php
use Tobento\App\AppFactory;

// Create the app
$app = (new AppFactory())->createApp();

// Adding boots
// $app->boot(\Tobento\App\Spam\Boot\HttpSpamErrorHandler::class); // not needed to boot!
$app->boot(\Tobento\App\Spam\Boot\Spam::class);

// Run the app
$app->run();
```

The error handler will return a 422 Unprocessable Entity HTTP response if spam was detected.

You may create a custom [Error Handler With A Higher Priority](https://github.com/tobento-ch/app-http/#prioritize-error-handler) of ```3000``` as defined on the ```Tobento\App\Spam\Boot\HttpSpamErrorHandler::class``` to handle spam exceptions to fit your application.

## Events

**Available Events**

| Event | Description |
| --- | --- |
| ```Tobento\App\Spam\Event\SpamDetected::class``` | The event will dispatch **after** a spam has been detected |

**Supporting Events**

Simply, install the [App Event](https://github.com/tobento-ch/app-event) bundle.

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)