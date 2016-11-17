<?php

namespace App;

use Webmozart\PathUtil\Path;

/**
 * Config
 *
 * @author kumakura9213
 */
class Config extends \Noodlehaus\Config
{

    /**
     * Static method for loading a Config instance.
     *
     * @param  string $path
     * @return Config
     */
    public static function load($path)
    {
        return new static(Path::join(__DIR__, '..', 'config', $path));
    }
}
