# Exception Handler

🧰 PHP Exception Handler built on top of [Symfony Messenger](https://symfony.com/doc/current/messenger.html) component. 
Provides middlewares that allow you to easily re-raise exceptions, chain them, or handle with a dedicated handler.

[![Build Status](https://img.shields.io/github/actions/workflow/status/phphd/exception-handler/ci.yaml?branch=main)](https://github.com/phphd/exception-handler/actions?query=branch%3Amain)
[![Codecov](https://codecov.io/gh/phphd/exception-handler/graph/badge.svg?token=GZRXWYT55Z)](https://codecov.io/gh/phphd/exception-handler)
[![Psalm coverage](https://shepherd.dev/github/phphd/exception-handler/coverage.svg)](https://shepherd.dev/github/phphd/exception-handler)
[![Psalm level](https://shepherd.dev/github/phphd/exception-handler/level.svg)](https://shepherd.dev/github/phphd/exception-handler)
[![Packagist Downloads](https://img.shields.io/packagist/dt/phphd/exception-handler.svg)](https://packagist.org/packages/phphd/exception-handler)
[![Licence](https://img.shields.io/github/license/phphd/exception-handler.svg)](https://github.com/phphd/exception-handler/blob/main/LICENSE)

## Installation 📥

1. Install via composer

    ```sh
    composer require phphd/exception-handler
    ```

2. Enable the bundle in the `bundles.php`

    ```php
    PhPhD\ExceptionHandler\Bundle\PhdExceptionHandlerBundle::class => ['all' => true],
    ```

## Configuration ⚒️

To leverage features of this bundle, you should add `phd_exception_handler` middleware to the list:

```diff
framework:
    messenger:
        buses:
            command.bus:
                default_middleware: false
                middleware:
+                   - phd_exception_handler
                    - validation
                    - doctrine_transaction
```

The core principle of exception handling revolves around the idea that exceptions are dispatched to the corresponding
bus to be handled. There must be one exception bus per one origin bus.

The exception bus name convention is straightforward: `command.bus` exceptions are forwarded
into `command.exception.bus`.

```yaml
framework:
    messenger:
        buses:
            command.exception.bus:
                default_middleware: false
                middleware:
                    - phd_exception_rethrow_unhandled
                    - phd_exception_chaining
                    - phd_exception_result_filter
                    -   handle_message:
                            - true
```

Currently, there are few exception handling middlewares provided.

### Rethrowing unhandled

Middleware: `phd_exception_rethrow_unhandled`

In case if dispatched exception had not been handled it is rethrown back. The exception is considered as handled if
handler returns a response, or throws another exception.

### Exception chaining

Middleware: `phd_exception_chaining`

Implements automatic exceptions escalation logic with `#[RaiseAs]` attribute.

### Result filter

Middleware: `phd_exception_result_filter`

Filters out all null results of exception handlers.

## Usage 🚀

### Re-Raising Exceptions

The simplest use-case is defining `#[RaiseAs]` attribute on your exception class:

```php
use PhPhD\ExceptionHandler\Middleware\Chain\Escalator\RaiseAs;

#[RaiseAs(AccessDeniedHttpException::class, bus: 'api.exception.bus')]
final class NonWhiteListedUserException extends DomainException
{
}
```

In this example, any time `NonWhiteListedUserException` is thrown from an underlying handler,
it will be raised as `AccessDeniedHttpException`.

As you can see, there's required attribute bus option. Since some exceptions could be thrown from multiple different
contexts (hence, different buses), it is required to explicitly specify the bus from which the particular exception
must be raised, so that in other scenarios another exceptions could be escalated:

```php
use PhPhD\ExceptionHandler\Middleware\Chain\Escalator\RaiseAs;

#[RaiseAs(ImportLockedHttpException::class, bus: 'api.exception.bus')]
#[RaiseAs(RecoverableMessageHandlingException::class, bus: 'consumer.exception.bus')]
final class ImportLockedException extends RuntimeException
{
}
```

In this example, `ImportLockedException` could be thrown either in http context (`api.bus`), or in the mq consumer
context (`consumer.bus`). Therefore, raised exceptions are different.

### Manual Handling

The exception is dispatched down to your custom handlers, where you could either return a Response, throw a new
exception, or just log it and return `null` so that exception will be re-thrown again.

```php
#[AsMessageHandler('api.exception.bus')]
final readonly class InventoryExceptionHandler
{
    /** @throws Throwable */
    public function __invoke(InventoryDomainException $exception, InventoryCommand $command): ?Response
    {
        if ($exception instanceof JournalHasUnInventoriedItemException) {
            $data = $this->formatJournalException($exception);

            return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
        }

        if ($exception instanceof StockItemNotVaildatedException) {
            $data = $this->formatItemException($exception, $command->getJournal());

            throw new StockItemNotValidatedHttpException($data, $exception);
        }

        return null;
    }
}
```

If you would like to use the same exception handler for multiple exception buses, you can do so by adding multiple
`#[AsMessageHandler]` attributes:

```php
#[AsMessageHandler(bus: 'command.exception.bus')]
#[AsMessageHandler(bus: 'query.exception.bus')]
final readonly class InventoryExceptionHandler
{
    // ...
}
```
