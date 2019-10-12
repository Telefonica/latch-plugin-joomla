<?php
/**
 * @package     Latch
 * @subpackage  Module.Admin.Installer
 *
 * @copyright   Copyright (C) 2013-2019 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Installer.
 *
 * @package     Latch
 * @subpackage  Module.Admin.Installer
 * @since       1.0
 */
class Mod_Admin_LatchInstallerScript
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
		$query = "update `#__extensions` set enabled=1 where type = 'module' and element = 'mod_admin_latch'";
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$db->query();

		// Module assignment
		$query = "insert into `#__modules_menu` (menuid, moduleid)"
			. " select 0 as menuid, id as moduleid"
			. " from `#__modules`"
			. " where module = 'mod_admin_latch'";
		$db->setQuery($query);
		$db->query();

		// Module default location
		$query = "update `#__modules` set position='cpanel',ordering=1,published=1,access=3 where module = 'mod_admin_latch'";
		$db = JFactory::getDBO();
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
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$db->query();
	}
}
