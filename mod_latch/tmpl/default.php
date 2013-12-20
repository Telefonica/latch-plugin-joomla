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

<div class="latch-container">
<form method="POST">
    <?php if ($paired): ?>
        <p>Your account is protected with Latch.</p>
        <input type="hidden" name="latchAction" value="unpair">
        <input type="submit" value="Unpair account">
		<?php echo JHtml::_( 'form.token' ); ?>
    <?php else: ?>
        <?php if (isset($userWantsToPairAccount) && $userWantsToPairAccount): ?>
            <label for="pairingToken">Type your pairing token:</label>
            <input type="text" name="pairingToken" class="small-input"><br>
            <div style="display: block; margin: 0 auto;">
                <button type="submit">Submit</button>
            </div>
        <?php else: ?>
            <p>Your account is unprotected</p>
            <input type="hidden" name="latchAction" value="pair">
            <button type="submit" class="latch-button">Pair account</button>
        <?php endif; ?>
    <?php endif; ?>
</form>
</div>


