<?php
/**
 * @copyright Copyright (C) 2010 redCOMPONENT.com. All rights reserved.
 * @license GNU/GPL, see license.txt or http://www.gnu.org/copyleft/gpl.html
 * Developed by email@recomponent.com - redCOMPONENT.com
 *
 * redSHOP can be downloaded from www.redcomponent.com
 * redSHOP is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * You should have received a copy of the GNU General Public License
 * along with redSHOP; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view' );

class barcodeViewbarcode extends JView
{
	function display($tpl = null)
	{
        JToolBarHelper::title(JText::_('COM_REDSHOP_BARCODE' ),'redshop_order48');

        $order_id = JRequest::getInt('order_id',0);

        $model = $this->getModel();
		$this->logData = $model->getLog($order_id);
		$this->logDetail = $model->getLogdetail($order_id);

    	parent::display($tpl);
    }
}
