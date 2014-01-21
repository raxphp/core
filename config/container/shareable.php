<?php

/**
 * Shareable configuration.
 *
 * All services are shareable by default, i.e. when an service is created for the
 * first time, it is stored in the container so it may be re-used again. You can
 * override this behaviour to force the container to build a new instance of the
 * service every time it is requested.
 *
 *     // Disable service sharing by nickname
 *     'foo' => false,
 *
 *     // By FQN
 *     'Vendor\\Namespace\\Foo' => false,
 */
return array();
