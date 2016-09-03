<?php
/**
 * @package     Latch
 * @subpackage  Library
 *
 * @copyright   Copyright (C) 2013-2014 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Ensure that autoloaders are set
JLoader::setup();

// Global libraries autoloader
JLoader::registerPrefix('Latch', dirname(__FILE__));

$composerAutoload = __DIR__ . '/vendor/autoload.php';

if (file_exists($composerAutoload))
{
	$loader = require_once $composerAutoload;
}

// Load library language
$lang = JFactory::getLanguage();
$lang->load('lib_latch', JPATH_SITE);
