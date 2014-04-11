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

// We don't use autoload to avoid changing standard SDK
if (!class_exists('Latch'))
{
	require_once dirname(__FILE__) . '/sdk/Error.php';
	require_once dirname(__FILE__) . '/sdk/LatchResponse.php';
	require_once dirname(__FILE__) . '/sdk/Latch.php';
}

// Load library language
$lang = JFactory::getLanguage();
$lang->load('lib_latch', JPATH_SITE);
