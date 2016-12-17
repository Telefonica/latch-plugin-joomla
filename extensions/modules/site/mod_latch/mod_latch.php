<?php
/**
 * @package     Latch
 * @subpackage  Module.Site
 *
 * @copyright   Copyright (C) 2013-2014 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::import('latch.library');

use ElevenPaths\Latch\Joomla\Helper\LatchHelper;

// Load module language
$lang = JFactory::getLanguage();
$lang->load('mod_latch', JPATH_SITE);

// Add CSS files
//JFactory::getDocument()->addStyleSheet(JUri::root() . "modules/mod_latch/latch.css");
JHtml::stylesheet('mod_latch/latch.css', false, true, false);

// Generate all the variables needed in the view
$user = JFactory::getUser();
$application = JFactory::getApplication();
$input = $application->input;
$latchAction = $input->get("latchAction", false);
$pairingToken = $input->get("pairingToken", false);
$userWantsToPairAccount = ($latchAction == "pair");

if ($pairingToken) {
    JSession::checkToken() or die( 'Invalid Token' );
    if (LatchHelper::pair($pairingToken)) {
        $application->enqueueMessage('Account paired successfully.');
    } else {
        $application->enqueueMessage('Error pairing account.', 'warning');
    }
} elseif ($latchAction) {
    JSession::checkToken() or die( 'Invalid Token' );
    if ($latchAction == "pair") {
        $userWantsToPairAccount = true;
    } elseif ($latchAction == "unpair") {
        if (!LatchHelper::unpair()) {
            $application->enqueueMessage('Error unpairing account.', 'warning');
        } else {
            $application->enqueueMessage('Account unpaired successfully.');
        }
    }
}
$paired = (LatchHelper::getLatchId($user->id) != NULL);

// Load the view
require JPATH_ROOT . "/modules/mod_latch/tmpl/default.php";
