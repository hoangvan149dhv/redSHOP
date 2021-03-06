<?php

/**
 * @package     RedSHOP.Backend
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

$isCreditCard   = 0;
$paymentMethods = RedshopHelperOrder::getPaymentMethodInfo('', false);

$isCompany      = $this->billing->is_company;

$paymentMethodId = $this->payment_detail->payment_method_class ?? 0;

if (count($paymentMethods) == 1) {
    $paymentMethodId = $paymentMethods[0]->element;
} ?>
<div>
    <?php if (count($paymentMethods) > 0): ?>
        <?php
        for ($p = 0, $pn = count($paymentMethods); $p < $pn; $p++) {
            $paymentParams = new \JRegistry($paymentMethods[$p]->params);

            $checked = "";
            if ($paymentMethodId == $paymentMethods[$p]->element) {
                $checked = "checked";
            }
            $private_person = $paymentParams->get('private_person', '');
            $isCreditCard   = $paymentParams->get('is_creditcard', '');
            $business       = $paymentParams->get('business', '');

            // Check for bank transfer payment type plugin - `rs_payment_banktransfer` suffixed
            $isBankTransferPaymentType = RedshopHelperPayment::isPaymentType($paymentMethods[$p]->element);

            if ($paymentMethods[$p]->element == 'rs_payment_eantransfer' || $isBankTransferPaymentType) {
                if ($isCompany == 0 && $private_person == 1) {
                    ?>
                    <label>
                        <input type="radio" name="payment_method_class"
                               value="<?php echo $paymentMethods[$p]->element; ?>" <?php echo $checked; ?> />
                        <?php echo JText::_($paymentMethods[$p]->name); ?>
                    </label><br>
                    <?php
                } else {
                    if ($isCompany == 1 && $business == 1) {
                        ?>
                        <label>
                            <input type="radio" name="payment_method_class"
                                   value="<?php echo $paymentMethods[$p]->element; ?>" <?php echo $checked; ?> />
                            <?php echo JText::_($paymentMethods[$p]->name); ?>
                        </label><br>
                        <?php
                    }
                }
            } else {
                ?>
                <label>
                    <input type="radio" name="payment_method_class"
                           value="<?php echo $paymentMethods[$p]->element; ?>" <?php echo $checked; ?> />
                    <?php echo JText::_($paymentMethods[$p]->name); ?>
                </label><br>
                <?php
            }
            if ($isCreditCard == 1) {
                $isCreditCard = 1;
            }
        }
        ?>
    <?php else: ?>
        <?php echo JText::_('COM_REDSHOP_NO_PAYMENT_METHOD_TO_DISPLAY') ?>
    <?php endif; ?>
</div>
