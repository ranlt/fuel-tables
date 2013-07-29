<?php

/**
 * Part of the Gasoline framework
 *
 * @package     Gasoline
 * @namespace   Table
 * @version     0.1-dev
 * @author      Gasoline Development Team
 * @author      Haro "WanWizard" Verton
 * @license     MIT License
 * @copyright   2013 Gasoline Development Team
 * @link        http://hubspace.github.io/gasoline
 */

Autoloader::add_classes(array(
    // Base
    'Table\\Table' => __DIR__ . '/classes/table.php',
    
    // Table Groups
    'Table\\Group' => __DIR__ . '/classes/group.php',
    'Table\\Group_Body' => __DIR__ . '/classes/group/body.php',
    'Table\\Group_Head' => __DIR__ . '/classes/group/head.php',
    'Table\\Group_Foot' => __DIR__ . '/classes/group/foot.php',
    
    // Helpers
    'Table\\Helpers' => __DIR__ . '/classes/helpers.php',
    
    // Exceptions
    'Table\\ReadOnlyException'      => __DIR__ . '/classes/exceptions.php',
    'Table\\OutOfBoundsException'   => __DIR__ . '/classes/exceptions.php',
));

/* End of file bootstrap.php */
/* Location: ./fuel/packages/table/bootstrap.php */

