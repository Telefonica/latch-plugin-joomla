<?php
/**
 * @package     Latch
 * @subpackage  Module.Site
 *
 * @copyright   Copyright (C) 2013-2019 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::import('latch.library');

use ElevenPaths\Latch\Joomla\Helper\LatchHelper;

// Load module language
$lang = Factory::getLanguage();
$lang->load('mod_latch', __DIR__);

// Add CSS files
JHtml::stylesheet('mod_latch/latch.css', false, true, false);

// Generate all the variables needed in the view
$user = JFactory::getUser();
$application = JFactory::getApplication();
$input = $application->input;
$latchAction = $input->get("latchAction", false);
$pairingToken = $input->get("pairingToken", false);
$userWantsToPairAccount = ($latchAction == "pair");

if ($pairingToken)
{
	JSession::checkToken() or die('Invalid Token');

	if (LatchHelper::pair($pairingToken))
	{
		$application->enqueueMessage('Account paired successfully.');
	}
	else
	{
		$application->enqueueMessage('Error pairing account.', 'warning');
	}
}
elseif ($latchAction)
{
	JSession::checkToken() or die('Invalid Token');

	if ($latchAction == "pair")
	{
		$userWantsToPairAccount = true;
	}
	elseif ($latchAction == "unpair")
	{
		if (!LatchHelper::unpair())
		{
			$application->enqueueMessage('Error unpairing account.', 'warning');
		}
		else
		{
			$application->enqueueMessage('Account unpaired successfully.');
		}
	}
}

$paired = (null !== LatchHelper::getLatchId($user->id));

// Load the view
require JPATH_ROOT . "/modules/mod_latch/tmpl/default.php";
