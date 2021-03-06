<?php

/**
 * @package     RedSHOP.Backend
 * @subpackage  Template
 *
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die;

$user = JFactory::getUser();

$start = $this->pagination->limitstart;
$end   = $this->pagination->limit;
?>
<form action="index.php?option=com_redshop" method="post" name="adminForm" id="adminForm">
    <div id="editcell">
        <table width="100%">
            <tr>
                <td><?php echo JText::_('COM_REDSHOP_DATE') . ": " . $this->lists['filteroption']; ?></td>
                <td><?php echo JText::_('COM_REDSHOP_TYPE') . ": " . $this->lists['typeoption']; ?></td>
            </tr>
        </table>
        <table class="adminlist table table-striped" width="100%">
            <thead>
            <tr>
                <th align="center"><?php echo JText::_('COM_REDSHOP_HASH'); ?></th>
                <th align="center"><?php echo JText::_('COM_REDSHOP_PRODUCT_NAME'); ?></th>
                <th align="center"><?php echo JText::_('COM_REDSHOP_PRODUCT_PRICE'); ?></th>
                <th align="center"><?php if ($this->typeoption == 2) {
                        echo JText::_('COM_REDSHOP_NUMBER_OF_ITEMS_SOLD');
                    } else {
                        echo JText::_('COM_REDSHOP_NUMBER_OF_TIMES_SOLD');
                    } ?>
                </th>
            </tr>
            </thead>
            <?php
            $disdate = "";

            for ($i = $start, $j = 0; $i < ($start + $end); $i++, $j++) {
                if (!isset($this->bestsell[$i]) || !is_object($this->bestsell[$i])) {
                    break;
                }

                $row = $this->bestsell[$i];

                if ($this->filteroption && $this->filteroption != 0 && $row->viewdate != $disdate) {
                    $disdate = $row->viewdate; ?>
                    <tr>
                        <td colspan="4"><?php echo JText::_("COM_REDSHOP_DATE") . ": " . $disdate; ?></td>
                    </tr>
                    <?php
                }
                $link = JRoute::_(
                    'index.php?option=com_redshop&view=product_detail&task=edit&cid[]=' . $row->product_id
                );
                if ($row->product_id != null) {
                    ?>
                    <tr>
                        <td align="center"><?php echo $i + 1; ?></td>
                        <td><a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_REDSHOP_EDIT_PRODUCT'); ?>">
                                <?php echo $row->product_name; ?></a></td>
                        <td align="center"><?php echo RedshopHelperProductPrice::formattedPrice(
                                $row->product_price
                            ); ?></td>
                        <td align="center"><?php echo $row->totalproduct; ?></td>
                    </tr>
                    <?php
                }
            }
            ?>
            <tfoot>
            <td colspan="4">
                <?php if (version_compare(JVERSION, '3.0', '>=')): ?>
                    <div class="redShopLimitBox">
                        <?php echo $this->pagination->getLimitBox(); ?>
                    </div>
                <?php endif; ?>
                <?php echo $this->pagination->getListFooter(); ?></td>
            </tfoot>
        </table>
    </div>
    <input type="hidden" name="view" value="statistic"/>
    <input type="hidden" name="layout" value="<?php echo $this->layout; ?>"/>
    <input type="hidden" name="boxchecked" value="0"/>
</form>
