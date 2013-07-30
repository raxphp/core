<?php

namespace Rax\Data\Loader\Base;

/**
 * Data loader interface.
 *
 * @author  Gregorio Ramirez <goyocode@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
interface BaseLoaderInterface
{
    /**
     * Loads the data from source.
     *
     * @param string $name
     *
     * @return array
     */
    public function load($name);

    /**
     * Save data to source.
     *
     * @param array $data
     */
    public function save(array $data);
}
