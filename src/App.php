<?php

declare(strict_types=1);

namespace App;

use AnzuSystems\Contracts\AnzuApp;

/**
 * Collection of globally available IMMUTABLE static helper functions.
 */
final class App extends AnzuApp
{
    public const ZERO = 0;
    public const SYSTEM = 'weather';
    public const ENTITY_NAMESPACE = __NAMESPACE__ . '\Entity';
    public const JSON_TYPE = 'json';

    public const FETCH_EXTRA_LAZY = 'EXTRA_LAZY';
}
