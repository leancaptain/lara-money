# Roadmap

`leancaptain/lara-money` is a thin Laravel integration layer for `leancaptain/money`.

Its purpose is to provide Laravel-specific integration without duplicating or weakening the framework-agnostic Money domain model.

The dependency direction must remain:

```text
Laravel application
        ↓
leancaptain/lara-money
        ↓
leancaptain/money
```

## Current foundation

The initial package provides:

* Laravel package auto-discovery
* package configuration
* optional configuration publishing
* `MoneyFactory` container binding
* Eloquent `MoneyCast`
* default-currency support
* per-record currency support
* consumer-defined currency resolution through `CurrencyResolver`
* Laravel-focused tests

## Near-term focus

Early releases should primarily harden the existing integration rather than expand the feature surface.

Likely areas include:

* broader Eloquent integration tests using real database tables
* improved package-specific exception handling
* edge cases around nullable values and currency attributes
* better custom-currency resolver ergonomics
* clearer documentation for single-currency and multi-currency applications
* compatibility testing across supported Laravel releases

## Possible future integrations

Features may be considered when repeated Laravel application usage demonstrates a clear need, including:

* Laravel validation rules for monetary input
* improved value-object casting ergonomics
* integration with Laravel's `Castable` APIs where beneficial
* additional casting strategies for proven use cases

These are possibilities rather than commitments.

## Features deliberately not planned by default

The package should avoid adding convenience APIs merely because Laravel makes them possible.

Unless strong real-world demand emerges, the package should not grow into a collection of:

* facades
* global money helpers
* Blade directives
* migration macros
* exchange-rate services
* accounting functionality
* payment integrations
* UI-framework-specific components
* Filament, Nova, or Livewire integrations

Such integrations can live in separate packages if they eventually justify their own existence.

## Versioning direction

### `0.0.x`

Stabilize the initial Laravel adapter.

Focus on correctness, tests, exceptions, documentation, and small API improvements.

### `0.1.x`

A more mature integration API informed by usage in multiple Laravel applications.

New features should only be added when they represent recurring integration needs.

### `1.0.0`

The goal for `1.0.0` is a stable Laravel-facing API around:

* configuration
* `MoneyFactory` container integration
* Eloquent money casting
* currency resolution
* predictable persistence semantics
* custom consumer currencies

## Relationship with `leancaptain/money`

`leancaptain/lara-money` may depend on compatible releases of `leancaptain/money`.

The core package must never depend on, reference, or otherwise become aware of Laravel or `lara-money`.

Laravel-specific behavior belongs here. Money-domain behavior belongs in `leancaptain/money`.

## Guiding principle

If the same Laravel-specific money integration problem appears repeatedly across real applications, it may belong in this package.

If only one application needs it, it should usually remain application code until broader reuse is proven.
