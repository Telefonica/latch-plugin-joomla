<?php
/**
 * @package     Latch
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2013-2019 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::import('latch.library');

use ElevenPaths\Latch\LatchResponse;
use ElevenPaths\Latch\Joomla\Helper\LatchHelper;

/**
 * Latch user plugin
 *
 * @package     Latch
 * @subpackage  Plugin
 * @since       1.0
 */
class PlgUserLatch extends JPlugin
{
	/**
	 * Login JRoute.
	 *
	 * @const
	 */
	const LOGIN_ROUTE = 'index.php?option=com_users&view=login';

	/**
	 * Joomla application
	 *
	 * @var  CMSApplication
	 */
	protected $app;

	/**
	 * Database connection.
	 *
	 * @var  \JDatabaseDriver
	 */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param   object  $subject  The object to observe
	 * @param   array   $config   An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
		$this->app = JFactory::getApplication();
		$this->db = JFactory::getDbo();
	}

	/**
	 * This method should handle any login logic and report back to the subject
	 *
	 * @param   array  $user     Holds the user data
	 * @param   array  $options  Array holding options (remember, autoregister, group)
	 *
	 * @return  boolean  True on success
	 */
	public function onUserLogin($user, $options = array())
	{
		$input = $this->app->input;
		$session = JFactory::getSession();

		if ($session->get("latch_twoFactor"))
		{
			$storedTwoFactor = $session->get("latch_twoFactor");
			$session->clear("latch_twoFactor");

			if ($input->get("latchTwoFactor", "") != $storedTwoFactor)
			{
				$this->makeLoginFail();
			}
		}

		if ($this->isAccountBlockedByLatch())
		{
			$this->makeLoginFail();
		}

		return true;
	}

	/**
	 * Make the login fail.
	 *
	 * @return  void
	 */
	private function makeLoginFail()
	{
		// The user cannot be authenticated yet
		JFactory::getSession()->destroy();
		$this->app->enqueueMessage(JText::_('JGLOBAL_AUTH_INVALID_PASS'), 'warning');
		$redirectJRoute = ($this->app->isAdmin()) ? $_SERVER['SCRIPT_NAME'] : self::LOGIN_ROUTE;
		$this->app->redirect(JRoute::_($redirectJRoute, false));
	}

	/**
	 * Check if the accoutn is blocked by latch.
	 *
	 * @return  boolean
	 */
	private function isAccountBlockedByLatch()
	{
		$userId = $this->retrieveUserId();
		$latchId = LatchHelper::getLatchId($userId);

		if (null === $latchId)
		{
			return fase;
		}

		$status = $this->getLatchStatus($latchId);

		if (isset($status['twoFactor']))
		{
			JFactory::getSession()->set("latch_twoFactor", $status['twoFactor']);
			$this->loadTwoFactorForm();
		}
		else
		{
			return $status['accountBlocked'];
		}

		return false;
	}

	/**
	 * Retreive the user ID.
	 *
	 * @return  integer
	 */
	private function retrieveUserId()
	{
		$input = $this->app->input;
		$username = $input->get("username", false);
		$query = $this->db->getQuery(true)
			->select('id')
			->from('#__users')
			->where('username=' . $this->db->quote($username));

		$this->db->setQuery($query);
		$result = $this->db->loadObject();

		return ($result) ? $result->id : 0;
	}

	/**
	 * Get latch status.
	 *
	 * @param   string  $latchId  Latch user identifier
	 *
	 * @return  array
	 */
	private function getLatchStatus($latchId)
	{
		$api = LatchHelper::getLatchConnection();

		if (null !== $api)
		{
			$response = $api->status($latchId);

			if ($this->isLatchResponseValid($response))
			{
				$appId = $this->params->get("latch_appID");
				$status = $response->getData()->{"operations"}->{$appId}->{"status"};
				$adaptedResponse = array('accountBlocked' => ($status == "off"));

				if (property_exists($response->getData()->{"operations"}->{$appId}, "two_factor"))
				{
					$adaptedResponse['twoFactor'] = $response->getData()->{"operations"}->{$appId}->{"two_factor"}->{"token"};
				}

				return $adaptedResponse;
			}
		}

		return array('accountBlocked' => false);
	}

	/**
	 * Load the two factor form.
	 *
	 * @return  void
	 */
	private function loadTwoFactorForm()
	{
		$input = $this->app->input;

		if ($this->app->isAdmin())
		{
			$loginFormAction = JRoute::_('index.php');
			$task = 'login';
			$passwordField = 'passwd';
			$password = $input->get($passwordField, false);
		}
		elseif ($this->app->isSite())
		{
			$loginFormAction = JRoute::_(self::LOGIN_ROUTE, false);
			$task = 'user.login';
			$passwordField = 'password';
			$password = $input->get($passwordField, false);
		}

		$username = $input->get("username", false);
		$return = $input->get("return", false);
		include 'twoFactorForm.php';
		die();
	}

	/**
	 * Check if latch response is valid.
	 *
	 * @param   LatchResponse   $response  Response from Latch API
	 *
	 * @return  boolean
	 */
	private function isLatchResponseValid(LatchResponse $response)
	{
		$appId = $this->params->get("latch_appID");
		$data = $response->getData();

		return $data != null &&
				property_exists($data, "operations") &&
				property_exists($data->{"operations"}, $appId) &&
				property_exists($data->{"operations"}->{$appId}, "status") &&
				$response->getError() == null;
	}
}
