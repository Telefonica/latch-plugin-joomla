<?php
/**
 * @package     Latch
 * @subpackage  Module.Site.Installer
 *
 * @copyright   Copyright (C) 2013-2019 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Installer.
 *
 * @package     Latch
 * @subpackage  Module.Site.Installer
 * @since       1.0
 */
class Mod_LatchInstallerScript
{
	/**
	 * Called on installation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  void
	 */
	public function install(JAdapterInstance $adapter)
	{
		// Enable module
		$query = "update `#__extensions` set enabled=1 where type = 'module' and element = 'mod_latch'";
		$db = JFactory::getDbo();
		$db->setQuery($query);
		$db->query();

		// Module assignment
		$query = "insert into `#__modules_menu` (menuid, moduleid)"
			. " select 0 as menuid, id as moduleid"
			. " from `#__modules` where module like 'mod_latch'";
		$db->setQuery($query);
		$db->query();

		// Module default location
		$query = "update `#__modules` set position='position-7',ordering=1,published=1,access=2 where module = 'mod_latch'";
		$db = JFactory::getDbo();
		$db->setQuery($query);
		$db->query();
	}

	/**
	 * Called on uninstallation
	 *
	 * @param   JAdapterInstance  $adapter  The object responsible for running this script
	 *
	 * @return  void
	 */
	public function uninstall(JAdapterInstance $adapter)
	{
		$query = "delete from `#__user_profiles` where profile_key = 'latch.id'";
		$db = JFactory::getDbo();
		$db->setQuery($query);
		$db->query();
	}
}
