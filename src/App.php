<?php

declare(strict_types=1);

namespace App;

use AnzuSystems\Contracts\AnzuApp;

/**
 * Collection of globally available IMMUTABLE static helper functions.
 */
final class App extends AnzuApp
{
    public const int ZERO = 0;
    public const string SYSTEM = 'weather';
    public const string ENTITY_NAMESPACE = __NAMESPACE__ . '\Entity';
    public const string JSON_TYPE = 'json';
    public const string DATE_TIME_ZONE = 'Europe/Bratislava';

    public const string FETCH_EXTRA_LAZY = 'EXTRA_LAZY';
    
    public const int BLOG_EXT_SYSTEM_ID = 4;
}
