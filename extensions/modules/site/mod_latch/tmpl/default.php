<?php
/**
 * @package     Latch
 * @subpackage  Module.Site
 *
 * @copyright   Copyright (C) 2013-2019 Eleven Paths. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;
?>

<div class="latch-container">
<form method="POST">
    <?php echo JHtml::_( 'form.token' ); ?>
    <?php if ($paired): ?>
        <p><?php echo JText::_('MOD_LATCH_ACCOUNT_PROTECTED'); ?></p>
        <input type="hidden" name="latchAction" value="unpair">
        <button class="latch-button" type="submit" ><?php echo JText::_('MOD_LATCH_UNPAIR_ACCOUNT'); ?></button>
    <?php else: ?>
        <?php if (isset($userWantsToPairAccount) && $userWantsToPairAccount): ?>
            <label for="pairingToken"><?php echo JText::_('MOD_LATCH_TYPE_PAIRING_TOKEN'); ?>:</label>
            <input type="text" name="pairingToken" class="small-input"><br>
            <div style="display: block; margin: 0 auto;">
                <button class="latch-button" type="submit"><?php echo JText::_('MOD_LATCH_SUBMIT'); ?></button>
            </div>
        <?php else: ?>
            <p><?php echo JText::_('MOD_LATCH_ACCOUNT_UNPROTECTED'); ?></p>
            <input type="hidden" name="latchAction" value="pair">
            <button type="submit" class="latch-button"><?php echo JText::_('MOD_LATCH_PAIR_ACCOUNT'); ?></button>
        <?php endif; ?>
    <?php endif; ?>
</form>
</div>


