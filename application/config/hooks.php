<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/

// hook for enable/disable profiling

$hook['pre_system'][] = array(
    'class'    => 'Profiler_enabler',
    'function' => 'enable_profiler',
    'filename' => 'Profiler_enabler.php',
    'filepath' => 'hooks',
    'params'   => array(),
);
