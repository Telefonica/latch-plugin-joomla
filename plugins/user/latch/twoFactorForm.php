<?php
/**
 * @package     Latch
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2013-2014 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;
?>
<html>
    <head>
        <style>
            .twoFactorContainer { display:block; width:300px; margin: 5% auto 0 auto; text-align: center; border: solid 1px rgb(184, 184, 184); border-radius:5px}
            .twoFactorHeader {float:left; background: #00b9be; color: #FFF; width:100%; border-top-left-radius: 5px; border-top-right-radius: 5px; font-family: sans-serif;}
            .twoFactorHeader h3 {text-align: center; margin-left: 10px;}
            .twoFactorForm {clear:left; padding-top:10px;}
            input {margin-top:10px}
            input[type="submit"] {width:65px;}
        </style>
    </head>
    <body>
        <div class="twoFactorContainer">
            <div class="twoFactorHeader">
                <h3>One-time password</h3>
            </div>
            <div class="twoFactorForm">
                <form method="POST" action="<?php echo $loginFormAction; ?>">
                <label for="nameTwoFactor">Insert your one-time password:</label>
                <input type="text" name="latchTwoFactor" id="latchTwoFactor">
                <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                <input type="hidden" name="<?php echo $passwordField; ?>" value="<?php echo htmlspecialchars($password); ?>">
                <input type="hidden" name="task" value="<?php echo htmlspecialchars($task); ?>">
                <input type="hidden" name="return" value="<?php echo htmlspecialchars($return); ?>">
                <?php echo JHtml::_('form.token'); ?>
                <button type="submit" name="Submit">Submit</button>
            </form>
            </div>
        </div>
    </body>
</html>

