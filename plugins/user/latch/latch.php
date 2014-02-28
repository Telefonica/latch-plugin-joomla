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

defined('_JEXEC') or die('Restricted access');

if (!class_exists('Latch')) {
    require_once(dirname(__FILE__) . '/sdk/Error.php');
    require_once(dirname(__FILE__) . '/sdk/LatchResponse.php');
    require_once(dirname(__FILE__) . '/sdk/Latch.php');
}

require_once JPATH_SITE . "/modules/mod_latch/helper.php";

class plgUserLatch extends JPlugin {

    private static $LOGIN_ROUTE = 'index.php?option=com_users&view=login';
    protected $app;
    protected $db;

    public function __construct(& $subject, $config) {
        parent::__construct($subject, $config);
        $this->loadLanguage();
        $this->app = JFactory::getApplication();
        $this->db = JFactory::getDbo();
    }

    public function onUserLogin($user, $options = array()) {
        $input = $this->app->input;
        $session = JFactory::getSession();
        if ($session->get("latch_twoFactor")) {
            $storedTwoFactor = $session->get("latch_twoFactor");
            $session->clear("latch_twoFactor");
            if ($input->get("latchTwoFactor", "") != $storedTwoFactor) {
                $this->makeLoginFail();
            }
        } elseif ($this->isAccountBlockedByLatch()) {
            $this->makeLoginFail();
        }
        return true;
    }

    private function makeLoginFail() {
        JFactory::getSession()->destroy(); // The user cannot be authenticated yet
        $this->app->enqueueMessage(JText::_('JGLOBAL_AUTH_INVALID_PASS'), 'warning');
        $redirectRoute = ($this->app->isAdmin()) ? $_SERVER['SCRIPT_NAME'] : self::$LOGIN_ROUTE;
        $this->app->redirect(JRoute::_($redirectRoute, false));
    }

    private function isAccountBlockedByLatch() {
        $userId = $this->retrieveUserId();
        $latchId = ModLatchHelper::getLatchId($userId);
        if ($latchId != NULL) {
            $status = $this->getLatchStatus($latchId);
            if (isset($status['twoFactor'])) {
                JFactory::getSession()->set("latch_twoFactor", $status['twoFactor']);
                $this->loadTwoFactorForm();
            } else {
                return $status['accountBlocked'];
            }
        }
        return false;
    }

    private function retrieveUserId() {
        $input = $this->app->input;
        $username = $input->get("username", false);
        $query = $this->db->getQuery(true)
                ->select('id')
                ->from('#__users')
                ->where('username=' . $this->db->quote($username));

        $this->db->setQuery($query);
        $result = $this->db->loadObject();
        return ($result) ? $result->id : NULL;
    }

    private function getLatchStatus($latchId) {
        $api = ModLatchHelper::getLatchConnection();
        if ($api != NULL) {
            $response = $api->status($latchId);
            if ($this->isLatchResponseValid($response)) {
                $appId = $this->params->get("latch_appID");
                $status = $response->getData()->{"operations"}->{$appId}->{"status"};
                $adaptedResponse = array('accountBlocked' => ($status == "off"));
                if (property_exists($response->getData()->{"operations"}->{$appId}, "two_factor")) {
                    $adaptedResponse['twoFactor'] = $response->getData()->{"operations"}->{$appId}->{"two_factor"}->{"token"};
                }
                return $adaptedResponse;
            }
        }
        return array('accountBlocked' => false);
    }

    private function loadTwoFactorForm() {
        $input = $this->app->input;
        if ($this->app->isAdmin()) {
            $loginFormAction = JRoute::_('index.php');
            $task = 'login';
            $passwordField = 'passwd';
            $password = $input->get($passwordField, false);
        } elseif($this->app->isSite()) {
            $loginFormAction = JRoute::_(self::$LOGIN_ROUTE, false);
            $task = 'user.login';
            $passwordField = 'password';
            $password = $input->get($passwordField, false);
        }
        $username = $input->get("username", false);
        $return = $input->get("return", false);
        include 'twoFactorForm.php';
        die();
    }

    private function isLatchResponseValid($response) {
        $appId = $this->params->get("latch_appID");
        $data = $response->getData();
        return $data != NULL &&
                property_exists($data, "operations") &&
                property_exists($data->{"operations"}, $appId) &&
                property_exists($data->{"operations"}->{$appId}, "status") &&
                $response->getError() == NULL;
    }

}
