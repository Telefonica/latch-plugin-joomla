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

if (!class_exists('Latch')) {
    require_once(dirname(__FILE__) . '/Error.php');
    require_once(dirname(__FILE__) . '/LatchResponse.php');
    require_once(dirname(__FILE__) . '/Latch.php');
}

class ModLatchHelper {

    private static $PROFILES_TABLE = '#__user_profiles';

    public static function pair($pairingToken) {
        if (self::getLatchId(JFactory::getUser()->id) != NULL) {
            // If the user is already paired, avoid API call
            return true;
        }
        $api = self::getLatchConnection();
        if ($api != NULL) {
            $response = $api->pair($pairingToken);
            if (self::containsLatchId($response)) {
                return self::storeLatchId($response);
            }
        }
        return false;
    }

    private static function containsLatchId($apiResponse) {
        return $apiResponse->getData() != NULL &&
                $apiResponse->getData()->{"accountId"} != NULL;
    }

    private static function storeLatchId($response) {
        $userId = JFactory::getUser()->id;
        $latchId = $response->getData()->{"accountId"};
        return self::insertLatchId($userId, $latchId);
    }

    public static function unpair() {
        $user = JFactory::getUser();
        $api = self::getLatchConnection();
        if ($api == NULL) {
            return false;
        } else {
            $latchId = self::getLatchId($user->id);
            if (!empty($latchId)) {
                $api->unpair($latchId);
                return self::removeLatchId($user->id);
            }
            return true;
        }
    }

    public static function getLatchConnection() {
        $pluginParams = new JRegistry(JPluginHelper::getPlugin("user", "latch")->params);
        $appId = $pluginParams->get("latch_appID");
        $appSecret = $pluginParams->get("latch_appSecret");
        $apiHost = $pluginParams->get("latch_host");
        if (!empty($apiHost)) {
            Latch::setHost(rtrim($apiHost, '/'));
        }
        if (!empty($appId) && !empty($appSecret)) {
            return new Latch($appId, $appSecret);
        }
        return NULL;
    }

    public static function getLatchId($userId) {
        if ($userId == NULL)
            return NULL;
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
                ->select($db->quoteName('profile_value'))
                ->from($db->quoteName(self::$PROFILES_TABLE))
                ->where(array(
            $db->quoteName('profile_key') . ' = ' . $db->quote('latch.id', false),
            $db->quoteName('user_id') . ' = ' . (int) $userId
        ));
        $db->setQuery($query);
        $result = $db->loadRow();
        return ($result == NULL) ? NULL : $result[0];
    }

    public static function insertLatchId($userId, $latchId) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $table = $db->quoteName(self::$PROFILES_TABLE);
        if (self::getLatchId($userId) == NULL) { // New record
            $query->insert($table)
                    ->columns($db->quoteName(array('user_id', 'profile_key', 'profile_value')))
                    ->values(implode(',', array((int) $userId, $db->quote('latch.id', false), $db->quote($latchId))));
        } else { // Update record
            $query->update($table)
                    ->set(array(
                        $db->quoteName('profile_value') . ' = ' . $db->quoteName($latchId),
                    ))
                    ->where(array(
                        $db->quoteName('profile_key') . ' = ' . $db->quote('latch.id'),
                        $db->quoteName('user_id') . ' = ' . (int) $userId
            ));
        }
        $db->setQuery($query);
        return $db->execute();
    }

    public static function removeLatchId($userId) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        if (self::getLatchId($userId) != NULL) {
            $query->delete(self::$PROFILES_TABLE)
                    ->where(array(
                        $db->quoteName('profile_key') . ' = ' . $db->quote('latch.id', false),
                        $db->quoteName('user_id') . ' = ' . (int) $userId
            ));
            $db->setQuery($query);
            return $db->execute();
        }
        return false;
    }

}
