<?php
/**
 * @package     Latch.Library
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2013-2019 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Update script class.
 * Note: This class doesn't use autoloading naminb conventions to allow to use always the latest version
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class LatchInstallerUpdateScript
{
	/**
	 * The installer.
	 *
	 * @var  JInstaller
	 */
	protected $installer;

	/**
	 * Constructor.
	 *
	 * @param   JInstaller  $installer  The installer
	 */
	public function __construct(JInstaller $installer)
	{
		$this->installer = $installer;
	}

	/**
	 * Do something before the update happens.
	 *
	 * @param   object  $parent  class calling this method
	 *
	 * @return  boolean
	 *
	 * @throws  RuntimeException  If something goes wrong
	 */
	public function preflight($parent)
	{
		return true;
	}

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
		return true;
	}

	/**
	 * Delete files.
	 *
	 * @param   array  $files  Files to delete
	 *
	 * @return  void
	 */
	protected function deleteFiles(array $files)
	{
		$files = (array) $files;

		array_map(
			function ($file)
			{
				@unlink($file);
			},
			$files
		);
	}

	/**
	 * Delete folder recursively
	 *
	 * @param   string  $folder  Folder to delete
	 *
	 * @return  boolean
	 */
	protected function deleteFolder($folder)
	{
		if (!is_dir($folder))
		{
			return true;
		}

		$files = glob($folder . '/*');

		foreach ($files as $file)
		{
			if (is_dir($file))
			{
				if (!$this->deleteFolder($file))
				{
					return false;
				}

				continue;
			}

			if (!@unlink($file))
			{
				return false;
			}
		}

		return rmdir($folder);
	}

	/**
	 * Delete folders recursively.
	 *
	 * @param   array  $folders  Folders
	 *
	 * @return  boolean
	 */
	protected function deleteFolders(array $folders)
	{
		foreach ($folders as $folder)
		{
			if (!$this->deleteFolder($folder))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Delete language files.
	 *
	 * @param   array   $extensions  Array containing the extensions base string. Example: array('mod_articles_news', 'plg_system_debug');
	 * @param   string  $basePath    Base folder where language files are. 'site' | 'admin'
	 *
	 * @return  void
	 */
	protected function deleteLanguageFiles($extensions, $basePath = 'site')
	{
		$filesToRemove = array();

		$extensions = (array) $extensions;

		foreach ($extensions as $extension)
		{
			$basePath = ($basePath === 'site' ? JPATH_SITE : JPATH_ADMINISTRATOR);

			if ($languageFiles = glob($basePath . '/language/*/*.' . $extension . '.*'))
			{
				$filesToRemove = array_merge($filesToRemove, $languageFiles);
			}
		}

		array_map('unlink', $filesToRemove);
	}
}
