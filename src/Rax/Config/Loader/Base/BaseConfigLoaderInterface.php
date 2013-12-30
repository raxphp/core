<?php

namespace Rax\Config\Loader\Base;

/**
 * Config loader interface.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
interface BaseConfigLoaderInterface
{
    /**
     * Saves the data to source.
     *
     * @param array $data
     */
    public function save(array $data);

    /**
     * Loads the config from source.
     *
     * @param string $key
     *
     * @return array
     */
    public function load($key);
}
