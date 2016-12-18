<?php
/**
 * @package     Latch.Package
 * @subpackage  Installer
 *
 * @copyright   Copyright (C) 2013-2016 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Package installer
 *
 * @since  __DEPLOY_VERSION__
 */
class Pkg_LatchInstallerScript
{
	/**
	 * Installer instance
	 *
	 * @var  JInstaller
	 */
	public $installer = null;

	/**
	 * Manifest of the extension being processed
	 *
	 * @var  SimpleXMLElement
	 */
	protected $manifest;

	/**
	 * Version installed.
	 *
	 * @var    string
	 */
	protected $installedVersion;

	/**
	 * List of update scripts
	 *
	 * @var    array
	 */
	private $updateScripts;

	/**
	 * Enable plugins if desired
	 *
	 * @param   object  $parent  class calling this method
	 *
	 * @return  void
	 */
	private function enablePlugins($parent)
	{
		// Required objects
		$manifest  = $this->getManifest($parent);

		if ($nodes = $manifest->files)
		{
			foreach ($nodes->file as $node)
			{
				$extType = (string) $node->attributes()->type;

				if ($extType != 'plugin')
				{
					continue;
				}

				$enabled = (string) $node->attributes()->enabled;

				if ($enabled !== 'true')
				{
					continue;
				}

				$extName  = (string) $node->attributes()->id;
				$extGroup = (string) $node->attributes()->group;

				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->update($db->quoteName("#__extensions"));
				$query->set("enabled=1");
				$query->where("type='plugin'");
				$query->where("element=" . $db->quote($extName));
				$query->where("folder=" . $db->quote($extGroup));
				$db->setQuery($query);
				$db->query();
			}
		}
	}

	/**
	 * Get the element of this extension from class name.
	 *
	 * @return  string
	 */
	private function getElement()
	{
		return strtolower(str_replace('InstallerScript', '', get_called_class()));
	}

	/**
	 * Get the current installed version.
	 *
	 * @return  string
	 */
	private function getInstalledVersion()
	{
		if (null === $this->installedVersion)
		{
			$this->loadInstalledVersion();
		}

		return $this->installedVersion;
	}

	/**
	 * Get the common JInstaller instance used to install all the extensions
	 *
	 * @return JInstaller The JInstaller object
	 */
	public function getInstaller()
	{
		if (null === $this->installer)
		{
			$this->installer = new JInstaller;
		}

		return $this->installer;
	}

	/**
	 * Getter with manifest cache support
	 *
	 * @param   JInstallerAdapter  $parent  Parent object
	 *
	 * @return  SimpleXMLElement
	 */
	protected function getManifest($parent)
	{
		if (null === $this->manifest)
		{
			$this->loadManifest($parent);
		}

		return $this->manifest;
	}

	/**
	 * Shit happens. Patched function to bypass bug in package uninstaller
	 *
	 * @param   JInstallerAdapter  $parent  Parent object
	 *
	 * @return  void
	 */
	protected function loadManifest($parent)
	{
		$element = strtolower(str_replace('InstallerScript', '', get_called_class()));
		$elementParts = explode('_', $element);

		// Type not properly detected or not a package
		if (count($elementParts) != 2 || strtolower($elementParts[0]) != 'pkg')
		{
			$this->manifest = $parent->get('manifest');

			return;
		}

		$manifestFile = __DIR__ . '/' . $element . '.xml';

		// Package manifest found
		if (file_exists($manifestFile))
		{
			$this->manifest = JFactory::getXML($manifestFile);

			return;
		}

		$this->manifest = $parent->get('manifest');
	}

	/**
	 * Load the installed version from the database.
	 *
	 * @return  self
	 */
	private function loadInstalledVersion()
	{
		// Reads current (old) version from manifest
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('e.manifest_cache'))
			->from($db->quoteName('#__extensions', 'e'))
			->where('e.element = ' . $db->quote($this->getElement()));

		$db->setQuery($query);

		$manifest = $db->loadResult();

		if (!$manifest)
		{
			return $this;
		}

		$manifest = json_decode($manifest);

		if (!is_object($manifest) || !property_exists($manifest, 'version'))
		{
			return $this;
		}

		$this->installedVersion = (string) $manifest->version;
	}

	/**
	 * Method to run after an install/update/discover method
	 *
	 * @param   object  $type    type of change (install, update or discover_install)
	 * @param   object  $parent  class calling this method
	 *
	 * @return  void
	 */
	public function postflight($type, $parent)
	{
		if ($type === 'update')
		{
			$this->runUpdateScriptsMethod($parent, 'postflight');
		}
		elseif (in_array($type, array('install', 'discover_install')))
		{
			return $this->enablePlugins($parent);
		}

		return true;
	}

	/**
	 * Method to run before an install/update/uninstall method
	 *
	 * @param   object  $type    type of change (install, update or discover_install)
	 * @param   object  $parent  class calling this method
	 *
	 * @return  boolean
	 */
	public function preflight($type, $parent)
	{
		if (version_compare(JVERSION, '3.0', 'lt'))
		{
			throw new RuntimeException("Latch only works with Joomla 3 series.");
		}

		$this->loadManifest($parent);

		if ($type === 'update')
		{
			$this->runUpdateScriptsMethod($parent, 'preflight');
		}

		return true;
	}

	/**
	 * Search a extension in the database
	 *
	 * @param   string  $element  Extension technical name/alias
	 * @param   string  $type     Type of extension (component, file, language, library, module, plugin)
	 * @param   string  $state    State of the searched extension
	 * @param   string  $folder   Folder name used mainly in plugins
	 *
	 * @return  integer           Extension identifier
	 */
	protected function searchExtension($element, $type, $state = null, $folder = null)
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('extension_id')
			->from($db->quoteName("#__extensions"))
			->where("type = " . $db->quote($type))
			->where("element = " . $db->quote($element));

		if (!is_null($state))
		{
			$query->where("state = " . (int) $state);
		}

		if (!is_null($folder))
		{
			$query->where("folder = " . $db->quote($folder));
		}

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Get the source folder where the install files are.
	 *
	 * @return  string
	 */
	public function getSourceFolder()
	{
		if (null === $this->sourceFolder)
		{
			$reflection = new \ReflectionClass($this);

			$this->sourceFolder = dirname($reflection->getFileName());
		}

		return $this->sourceFolder;
	}

	/**
	 * Get the path to the base updates folder
	 *
	 * @param   object  $parent  class calling this method
	 *
	 * @return  string
	 */
	private function getUpdatesFolder($parent)
	{
		$element = $this->manifest->xpath('//update');

		if (!$element)
		{
			return null;
		}

		$element = reset($element);

		$updatesFolder = $parent->getParent()->getPath('source');

		$folder = (string) $element->attributes()->folder;

		if ($folder && file_exists($updatesFolder . '/' . $folder))
		{
			$updatesFolder = $updatesFolder . '/' . $folder;
		}

		return $updatesFolder;
	}

	/**
	 * Get the instances of applicable update scripts
	 *
	 * @param   object  $parent  class calling this method
	 *
	 * @return  array
	 *
	 * @since   3.1.3
	 */
	private function getUpdateScripts($parent)
	{
		if (null !== $this->updateScripts)
		{
			return $this->updateScripts;
		}

		$this->updateScripts = array();

		// Require the base script installer if it doesn't exist.
		$baseScript = $parent->getParent()->getPath('source') . '/libraries/latch/installer/update.php';

		if (file_exists($baseScript))
		{
			require_once $baseScript;
		}

		$baseUpdatesFolder = $this->getUpdatesFolder($parent);

		if (!$baseUpdatesFolder)
		{
			return $this->updateScripts;
		}

		$updateFolders = $this->manifest->xpath('//update/scripts/folder');
		$updateFiles = array();

		// Collects all update files from the update folders
		foreach ($updateFolders as $updateFolder)
		{
			$updateFolderPath = $baseUpdatesFolder . '/' . $updateFolder;

			if (!$fileNames = JFolder::files($updateFolderPath))
			{
				continue;
			}

			foreach ($fileNames as $fileName)
			{
				$version = basename($fileName, '.php');
				$updateFiles[$version] = $updateFolderPath . '/' . $fileName;
			}
		}

		// Sort the files in ascending order
		uksort($updateFiles, 'version_compare');

		$currentVersion = $this->getInstalledVersion();

		foreach ($updateFiles as $version => $path)
		{
			if (version_compare($version, $currentVersion) <= 0)
			{
				continue;
			}

			require_once $path;

			$updateClassName = 'UpdateTo' . str_replace(['.', '-'], '', $version);

			if (class_exists($updateClassName))
			{
				$this->updateScripts[] = new $updateClassName($this->getInstaller());
			}

			$currentVersion = $version;
		}

		return $this->updateScripts;
	}

	/**
	 * Runs the update for the given version.
	 *
	 * @param   object  $parent  class calling this method
	 * @param   string  $method  Method to run from the update scripts
	 *
	 * @return  boolean
	 *
	 * @since   3.1.3
	 *
	 * @throws  RuntimeException  If something goes wrong in the method
	 */
	private function runUpdateScriptsMethod($parent, $method)
	{
		$updateScripts = $this->getUpdateScripts($parent);

		foreach ($updateScripts as $updateScript)
		{
			if (!method_exists($updateScript, $method))
			{
				continue;
			}

			$updateScript->$method($parent);
		}

		return true;
	}
}
