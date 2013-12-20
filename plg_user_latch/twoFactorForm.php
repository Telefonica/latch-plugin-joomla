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
?>

<html>
    <head>
        <style>
            .form-container {
                padding: 10px 0px 0px 10px;
                border-radius: 4px;
                border: #999 solid 1px;
                width: 450px;
                margin: 0 auto;
            }
        </style>
    </head>
    <body>
        <div class="form-container">
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
    </body>
</html>

