<?php
/**
 * @package     RedShop
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2005 - 2014 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

require_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/acyplugins.php';
require_once JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/list.php';

/**
 * Plugins RedITEM Category Fields
 *
 * @since  1.0
 */
class PlgRedshop_UserRegistration_Acymailing extends JPlugin
{
	/**
	 * autoAcymailingSubscription function
	 *
	 * @param   array  $data  data for trigger
	 * 
	 * @return boolean
	 */
	public function onAfterCreateRedshopUser($data = array())
	{
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->qn(array('subid')))
			->from($db->qn('#__acymailing_subscriber'))
			->where($db->qn('userid') . ' = ' . $db->quote($user->id));

		$db->setQuery($query);
		$sub = $db->loadObject();

		$plugin = JPluginHelper::getPlugin('redshop_user', 'registration_acymailing');
		$pluginParams = new JRegistry($plugin->params);

		$list = $pluginParams->get('listschecked');

		$query = $db->getQuery(true);
		$query->select($db->qn(array('listid')))
			->from($db->qn('#__acymailing_list'));

		switch ($list)
		{
			case 'None':
				return true;
				break;
			case 'All':
				break;
			default:
				$list = '(' . $list . ')';
				$query->where($db->qn('listid') . ' IN ' . $list);
				break;
		}

		$db->setQuery($query);
		$items = $db->loadObjectList();

		if (count($items))
		{
			foreach ($items as $item)
			{
				$date = JFactory::getDate()->toUnix();
				$query = $db->getQuery(true);
				$query->insert($db->qn('#__acymailing_listsub'))
					->columns($db->qn(array('listid', 'subid', 'subdate', 'status')))
					->values($db->quote($item->listid) . ',' . $db->quote($sub->subid) . ',' . $date . ',' . $db->quote('1'));

				$db->setQuery($query);
				$db->query();
			}
		}

		return true;
	}
}
