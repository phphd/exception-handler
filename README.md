# PhdExceptionHandlerBundle

🧰 Provides [Symfony Messenger](https://symfony.com/doc/current/messenger.html) middlewares tailored for exception
handling. You can easily re-raise exceptions, chain them, or handle with a dedicated bus.

[![Build Status](https://img.shields.io/github/actions/workflow/status/phphd/exception-handler-bundle/ci.yaml?branch=main)](https://github.com/phphd/exception-handler-bundle/actions?query=branch%3Amain)
[![Codecov](https://codecov.io/gh/phphd/exception-handler-bundle/graph/badge.svg?token=GZRXWYT55Z)](https://codecov.io/gh/phphd/exception-handler-bundle)
[![Psalm coverage](https://shepherd.dev/github/phphd/exception-handler-bundle/coverage.svg)](https://shepherd.dev/github/phphd/exception-handler-bundle)
[![Psalm level](https://shepherd.dev/github/phphd/exception-handler-bundle/level.svg)](https://shepherd.dev/github/phphd/exception-handler-bundle)
[![Packagist Downloads](https://img.shields.io/packagist/dt/phphd/exception-handler-bundle.svg)](https://packagist.org/packages/phphd/exception-handler-bundle)
[![Licence](https://img.shields.io/github/license/phphd/exception-handler-bundle.svg)](https://github.com/phphd/exception-handler-bundle/blob/main/LICENSE)

## Installation 📥

1. Install via composer

    ```sh
    composer require phphd/exception-handler-bundle
    ```

2. Enable the bundle in the `bundles.php`

    ```php
    PhPhD\ExceptionHandlerBundle\PhdExceptionHandlerBundle::class => ['all' => true],
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

The core principle of exception handling revolves around the idea of exceptions being dispatched to their corresponding
bus, therefore each message bus must define respective exception bus.

The exception bus name convention is straightforward:`command.bus` exceptions are forwarded
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

In case if dispatched exception had not been handled it is rethrown back.

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
#[RaiseAs(AccessDeniedHttpException::class, bus: 'command.exception.bus')]
final class NonWhiteListedUserException extends DomainException
{
}
```

In this example, any time `NonWhiteListedUserException` is thrown from an underlying handler,
it will be raised as `AccessDeniedHttpException`.

As you can see, there's required attribute bus option. Since some exceptions could be thrown from multiple different
scenarios (hence, different buses), it is required to explicitly specify the bus from which the particular exception
must be raised, so that in other scenarios another exceptions could be escalated:

```php
#[RaiseAs(ImportLockedHttpException::class, bus: 'query.exception.bus')]
#[RaiseAs(RecoverableMessageHandlingException::class, bus: 'consumer.exception.bus')]
final class ImportLockedException extends RuntimeException
{
}
```

In previous example, `ImportLockedException` could be thrown either in http context (`query.bus`), or in the consumer
context (`consumer.bus`). Therefore, raised exceptions are different.

### Manual Handling

The exception is dispatched down to your custom handlers, where you could either return a Response, throw a new
exception, or just return `null` so that exception will be thrown again.

```php
#[AsMessageHandler('command.exception.bus')]
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

If you would like to use the same exception handler for multiple exception buses, you can do so using following more
verbose message handler registration syntax:

```php
#[AutoconfigureTag('messenger.message_handler', [
    'handles' => InventoryDomainException::class, 
    'bus' => 'command.exception.bus',
])]
#[AutoconfigureTag('messenger.message_handler', [
    'handles' => InventoryDomainException::class, 
    'bus' => 'query.exception.bus'
])]
final readonly class InventoryExceptionHandler
{
    // ...
}
```
