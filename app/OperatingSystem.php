<?php

namespace App;

class OperatingSystem
{
    /**
     * Returns the current OS identifier.
     */
    public static function id(): string
    {
        if (static::onWindows()) {
            return 'win';
        } elseif (static::onMac()) {
            return static::macArchitectureId();
        }

        return 'linux';
    }

    /**
     * Determine if the operating system is Windows or Windows Subsystem for Linux.
     */
    public static function onWindows(): bool
    {
        return PHP_OS === 'WINNT' || str_contains(php_uname(), 'Microsoft');
    }

    /**
     * Determine if the operating system is macOS.
     */
    public static function onMac(): bool
    {
        return PHP_OS === 'Darwin';
    }

    /**
     * Mac platform architecture.
     */
    public static function macArchitectureId(): string
    {
        return match (php_uname('m')) {
            'arm64' => 'mac-arm',
            default => 'mac-intel',
        };
    }
}
