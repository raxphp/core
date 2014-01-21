<?php

/**
 * Alias configuration.
 *
 * An alias allows you to point an interface or class to a different one, which
 * is primarily useful when using interfaces.
 *
 * If you don't want to bind an object to one particular implementation, you can
 * use an interface as the type hint instead:
 *
 *     public function indexAction(PostInterface $post)
 *     {
 *
 * This will allow you to swap all $post objects with a different implementation
 * anytime by changing a single line in this configuration file:
 *
 *     'Vendor\\Namespace\\PostInterface' => 'Vendor\\Namespace\\PostEntity',
 *
 * Aliases can be set at runtime:
 *
 *     $container->addAlias('Vendor\\Namespace\\PostInterface', 'Vendor\\Namespace\\PostEntity');
 *
 * Use false to disable an alias upper in the configuration cascade.
 *
 *     'Vendor\\Namespace\\PostInterface' => false,
 */
return array();
