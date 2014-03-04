<?php

/*
Latch Joomla extension - Integrates Latch into the Joomla authentication process.
Copyright (C) 2013 Eleven Paths

This library is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once( dirname(__FILE__) . '/helper.php' );

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once( dirname(__FILE__) . '/helper.php' );

// Add CSS files
JFactory::getDocument()->addStyleSheet(JUri::root() . "modules/mod_latch/latch.css");

// Generate all the variables needed in the view
$user = JFactory::getUser();
$application = JFactory::getApplication();
$input = $application->input;
$latchAction = $input->get("latchAction", false);
$pairingToken = $input->get("pairingToken", false);
$userWantsToPairAccount = ($latchAction == "pair");

if ($pairingToken) {
    JSession::checkToken() or die( 'Invalid Token' );
    if (ModLatchHelper::pair($pairingToken)) {
        $application->enqueueMessage('Account paired succesfully.');
    } else {
        $application->enqueueMessage('Error pairing account.', 'warning');
    }
} elseif ($latchAction) {
    JSession::checkToken() or die( 'Invalid Token' );
    if ($latchAction == "pair") {
        $userWantsToPairAccount = true;
    } elseif ($latchAction == "unpair") {
        if (!ModLatchHelper::unpair()) {
            $application->enqueueMessage('Error unpairing account.', 'warning');
        } else {
            $application->enqueueMessage('Account unpaired succesfully.');
        }
    }
}
$paired = (ModLatchHelper::getLatchId($user->id) != NULL);

// Load the view
require JPATH_ROOT . "/modules/mod_latch/tmpl/default.php";
