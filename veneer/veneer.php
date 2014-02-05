<?php
/**
 * veneer - An Experimental API Framework for PHP
 *
 * @author     Ryan Uber <ru@ryanuber.com>
 * @copyright  Ryan Uber <ru@ryanuber.com>
 * @link       https://github.com/ryanuber/veneer
 * @license    http://opensource.org/licenses/MIT
 * @package    veneer
 * @category   api
 */

namespace veneer;

/**
 * Require various framework components in the proper order. These calls
 * will only load in classes, and won't actually execute anything. This
 * is intended to make using the framework simple for a developer - simply
 * include this file, then begin using veneer.
 */
require_once 'util.php';
\veneer\util::include_dir('exception');
require_once 'output.php';
\veneer\util::include_dir('output');
\veneer\util::include_dir(\veneer\util::path_join('output', 'handler'));
require_once \veneer\util::path_join('http', 'response.php');
require_once 'call.php';
require_once 'app.php';
require_once 'router.php';

?>
