<?php
/**
 * @package     Latch
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2013-2016 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

JLoader::import('latch.library');

use ElevenPaths\Latch\Joomla\Helper\LatchHelper;

/**
 * Latch user plugin
 *
 * @package     Latch
 * @subpackage  Plugin
 * @since       1.0
 */
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
        $latchId = LatchHelper::getLatchId($userId);
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
        $api = LatchHelper::getLatchConnection();
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
