<?php


/**
 * Service configuration.
 *
 * In general, a service is any object obtained through the container. This
 * configuration file exists so you can define complex services that cannot be
 * built automatically by the container.
 *
 * Services are defined as closures so heavy objects can be lazy loaded only when
 * absolutely needed. Plus the signature will give you a nice OOD hotspot.
 *
 *     // Use the class name of the object as the array key to make the container smarter
 *     'Vendor\\Namespace\\ClassName' => function(Something $something) {
 *         $className = new ClassName();
 *         $className->setSomething($something);
 *
 *         return $className;
 *     },
 */
return array();
