<?php

/**
 * The freshness level of the object.
 *
 * This setting determines whether a new object or singleton
 * is returned.
 *
 * Key: fqn or id, e.g. "Vendor\\Namespace\\Class" or "id"
 * Value: "new", "shared" or closure e.g.:
 *
 *     'foo' => function($id, $fqn = null) {
 *         return ... ? 'shared' : 'new';
 *     }
 *
 * This value can be overriden at the parameter level by specifying
 * the "new" prefix or ommiting for shared e.g.:
 *
 *     public function fooAction(Foo $newFoo, Bar $bar)
 */
return array(
    'freshness' => array(),
);
