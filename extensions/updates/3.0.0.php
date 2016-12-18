<?php
/**
 * @package     Latch.Package
 * @subpackage  Updates
 *
 * @copyright   Copyright (C) 2013-2016 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Update to version 3.0.0.
 *
 * @since  3.0.0
 */
class UpdateTo300 extends LatchInstallerUpdateScript
{
	/**
	 * Do something after the update happens.
	 *
	 * @param   object  $parent  class calling this method
	 *
	 * @return  boolean
	 *
	 * @throws  RuntimeException  If something goes wrong
	 */
	public function postflight($parent)
	{
		$this->deleteLanguageFiles(
			array(
				'mod_latch'
			)
		);

		$this->deleteLanguageFiles(
			array(
				'plg_user_latch'
			),
			'admin'
		);

		$this->deleteFolders(
			array(
				JPATH_SITE . '/libraries/latch/helper',
				JPATH_SITE . '/libraries/latch/sdk'
			)
		);

		$this->deleteFiles(
			array(
				JPATH_SITE . '/libraries/latch/LICENSE',
				JPATH_SITE . '/libraries/latch/composer.json',
				JPATH_SITE . '/libraries/latch/composer.lock',
				JPATH_SITE . '/modules/mod_latch/LICENSE',
				JPATH_SITE . '/plugins/user/latch/install.php',
				JPATH_SITE . '/plugins/user/latch/LICENSE'
			)
		);

		return true;
	}
}
