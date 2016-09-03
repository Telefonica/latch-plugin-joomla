<?php
/**
 * @package     Latch
 * @subpackage  Library
 *
 * @copyright   Copyright (C) 2013-2014 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace ElevenPaths\Latch\Joomla\Helper;

defined('_JEXEC') or die;

use ElevenPaths\Latch\Latch;

/**
 * Global Latch helper
 *
 * @since  1.0.0
 */
class LatchHelper
{
	/**
	 * @var  string
	 */
	private static $profilesTable = '#__user_profiles';

	/**
	 * Pair with latch
	 *
	 * @param   string  $pairingToken  Latch token
	 *
	 * @return  boolean
	 */
	public static function pair($pairingToken)
	{
		if (self::getLatchId(\JFactory::getUser()->id) != null)
		{
			// If the user is already paired, avoid API call
			return true;
		}

		$api = self::getLatchConnection();

		if ($api != null)
		{
			$response = $api->pair($pairingToken);

			if (self::containsLatchId($response))
			{
				return self::storeLatchId($response);
			}
		}

		return false;
	}

	/**
	 * Check if a Latch response contains an account id
	 *
	 * @param   LatchResponse  $response  Response from Latch
	 *
	 * @return  boolean
	 */
	private static function containsLatchId($response)
	{
		return $response->getData() != null && $response->getData()->{"accountId"} != null;
	}

	/**
	 * Save Latch identifier for active user
	 *
	 * @param   LatchResponse  $response  Response from Latch
	 *
	 * @return  boolean
	 */
	private static function storeLatchId($response)
	{
		$userId = \JFactory::getUser()->id;
		$latchId = $response->getData()->{"accountId"};

		return self::insertLatchId($userId, $latchId);
	}

	/**
	 * Pair account with Latch
	 *
	 * @return  boolean
	 */
	public static function unpair()
	{
		$user = \JFactory::getUser();
		$api = self::getLatchConnection();

		if ($api == null)
		{
			return false;
		}
		else
		{
			$latchId = self::getLatchId($user->id);

			if (!empty($latchId))
			{
				$api->unpair($latchId);

				return self::removeLatchId($user->id);
			}

			return true;
		}
	}

	/**
	 * Get a Latch instance
	 *
	 * @return  Latch  Connection instance
	 */
	public static function getLatchConnection()
	{
		$pluginParams = new \JRegistry(\JPluginHelper::getPlugin("user", "latch")->params);
		$appId = $pluginParams->get("latch_appID");
		$appSecret = $pluginParams->get("latch_appSecret");
		$apiHost = $pluginParams->get("latch_host");

		if (!empty($apiHost))
		{
			Latch::setHost(rtrim($apiHost, '/'));
		}

		if (!empty($appId) && !empty($appSecret))
		{
			return new Latch($appId, $appSecret);
		}

		return;
	}

	/**
	 * Get the latch identifier for an user
	 *
	 * @param   int  $userId  User identifier
	 *
	 * @return  mixed         Latch id / null for no result
	 */
	public static function getLatchId($userId)
	{
		if (null === $userId)
		{
			return;
		}

		$db = \JFactory::getDbo();
		$query = $db->getQuery(true)
				->select($db->quoteName('profile_value'))
				->from($db->quoteName(self::$profilesTable))
				->where(
					array(
						$db->quoteName('profile_key') . ' = ' . $db->quote('latch.id', false),
						$db->quoteName('user_id') . ' = ' . (int) $userId
					)
				);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Save user's latch identifier on user profile
	 *
	 * @param   int  $userId   User identifier
	 * @param   int  $latchId  Latch identifier to save
	 *
	 * @return  boolean
	 */
	public static function insertLatchId($userId, $latchId)
	{
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);
		$table = $db->quoteName(self::$profilesTable);

		// New record
		if (self::getLatchId($userId) == null)
		{
			$query->insert($table)
					->columns($db->quoteName(array('user_id', 'profile_key', 'profile_value')))
					->values(implode(',', array((int) $userId, $db->quote('latch.id', false), $db->quote($latchId))));
		}
		else
		// Update record
		{
			$query->update($table)
					->set(
						array(
							$db->quoteName('profile_value') . ' = ' . $db->quoteName($latchId),
						)
					)
					->where(
						array(
							$db->quoteName('profile_key') . ' = ' . $db->quote('latch.id'),
							$db->quoteName('user_id') . ' = ' . (int) $userId
						)
					);
		}

		$db->setQuery($query);

		return $db->execute();
	}

	/**
	 * Remove user's Latch Id
	 *
	 * @param   int  $userId  User identifier
	 *
	 * @return  boolean
	 */
	public static function removeLatchId($userId)
	{
		$db = \JFactory::getDbo();
		$query = $db->getQuery(true);

		if (self::getLatchId($userId) != null)
		{
			$query->delete(self::$profilesTable)
					->where(
						array(
							$db->quoteName('profile_key') . ' = ' . $db->quote('latch.id', false),
							$db->quoteName('user_id') . ' = ' . (int) $userId
						)
					);
			$db->setQuery($query);

			return $db->execute();
		}

		return false;
	}
}
