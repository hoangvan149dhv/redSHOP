<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2008 - 2017 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Redshop categories Model
 *
 * @package     Redshop.Backend
 * @subpackage  Models.Categories
 * @since       2.0.4
 */
class RedshopModelCategories extends RedshopModelList
{
	/**
	 * Name of the filter form to load
	 *
	 * @var  string
	 */
	protected $filterFormName = 'filter_categories';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   2.0.0.2
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'name', 'c.name',
				'description', 'c.description',
				'ordering', 'c.ordering',
				'id', 'c.id',
				'published', 'c.published',
				'lft', 'c.lft'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'c.lft', $direction = 'asc')
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', 0);
		$this->setState('filter.filter_category_id', $categoryId);

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.category_id');

		return parent::getStoreId($id);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 *
	 * @since   2.0.0.2
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
				->select('c.*')
				->select($db->qn('c.name', 'title'))
				->from($db->qn('#__redshop_category', 'c'));

		// Remove "ROOT" item
		$query->where($db->qn('level') . ' > ' . $db->quote('0'));

		// Filter: Parent ID
		$parentId = $this->getState('filter.category_id');

		switch (gettype($parentId))
		{
			case 'array':

				if (count($parentId))
				{
					$parentId = implode(',', $db->q($parentId));
					$query->where($db->quoteName('c.parent_id') . ' IN(' . $parentId . ')');
				}

				break;

			case 'string':
			case 'int':
			case 'integer':

				if ($parentId)
				{
					$query->where($db->quoteName('c.parent_id') . ' = ' . (int) $parentId);
				}
				break;

			default:
				break;
		}

		// Filter categories by "level"
		$level = (int) $this->getState('filter.level', 0);

		if (!empty($level))
		{
			$query->where($db->qn('c.level') . ' = ' . $level);
		}

		// Filter: Get deeper child or parent
		$lft = $this->getState('filter.lft', 0);
		$rgt = $this->getState('filter.rgt', 0);

		if (($lft) && ($rgt))
		{
			$query->where($db->qn('c.lft') . ' >= ' . (int) $lft);
			$query->where($db->qn('c.rgt') . ' <= ' . (int) $rgt);
		}

		// Filter by search in name.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->qn('c.id') . ' = ' . $db->q((int) substr($search, 3)));
			}
			else
			{
				$search = $db->q('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where($db->qn('c.name') . ' LIKE ' . $search);
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'c.lft');
		$orderDirn = $this->state->get('list.direction', 'ASC');

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Method to count category.
	 *
	 * @param   int  $cid  category id.
	 *
	 * @return  void.
	 */
	public function getProducts($cid)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(category_id)')
			->from($db->qn('#__redshop_product_category_xref'))
			->where($db->qn('category_id') . ' = ' . $db->q((int) $cid));

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Method to assign template for category.
	 *
	 * @param   array  $data  data for assign template.
	 *
	 * @return  void.
	 */
	public function assignTemplate($data)
	{
		$cid = $data['cid'];
		$categoryTemplate = $data['category_template'];

		if (count($cid))
		{
			$db = $this->getDbo();
			$fields = array(
				$db->qn('template') . ' = ' . $db->q((int) $categoryTemplate)
			);
			$conditions = array(
				$db->qn('id') . ' IN (' . implode(',', $cid) . ')'
			);
			$query = $db->getQuery(true)
				->update($db->qn('#__redshop_category'))
				->set($fields)
				->where($conditions);

			if (!$db->setQuery($query)->execute())
			{
				$this->setError($this->_db->getErrorMsg());

				return false;
			}
		}

		return true;
	}
}
