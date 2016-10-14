<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Redshop Category Model
 *
 * @package     Redshop.Backend
 * @subpackage  Models.Category
 * @since       2.0.0.2
 */
class RedshopModelCategory extends JModelAdmin
{
	/**
	 * Returns a Table object, always creating it
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A database object
	 *
	 * @since   2.0.0.2
	 */
	public function getTable($type = 'Category', $prefix = 'RedshopTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      Data for the form. [optional]
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not. [optional]
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   2.0.0.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_redshop.category',
			'category',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   2.0.0.2
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_redshop.edit.category.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		$this->preprocessData('com_redshop.category', $data);

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem($pk = null)
	{
		$item  = parent::getItem($pk);

		if (!empty($item->category_id))
		{
			$db = $this->getDBO();
			$query = $db->getQuery(true)
				->select('c.*')
				->select($db->qn('cx.category_parent_id'))
				->from($db->qn('#__redshop_category', 'c'))
				->leftJoin($db->qn('#__redshop_category_xref', 'cx') . ' ON ' . $db->qn('c.category_id') . ' = ' . $db->qn('cx.category_child_id'))
				->where($db->qn('category_id') . ' = ' . $db->q((int) $item->category_id));
			$data = $db->setQuery($query)->loadObject();

			$data->category_more_template = explode(',', $data->category_more_template);

			return $data;
		}
		else
		{
			$item->products_per_page = 5;

			return $item;
		}
	}

	/**
	 * Method to get extra fields to category.
	 *
	 * @param   integer  $item  The object category values.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   2.0.0.2
	 */
	public function getExtraFields($item)
	{
		$redshopTemplate = Redtemplate::getInstance();
		$template_desc = $redshopTemplate->getTemplate('category', $item->category_template, '', true);
		$template = $template_desc[0]->template_desc;
		$regex = '/{rs_[\w]{1,}\}/';
		preg_match_all($regex, $template, $matches);
		$listField = array();

		if (count($matches[0]) > 0)
		{
			$dbname = implode(',', $matches[0]);
			$dbname = str_replace(array('{', '}'), '', $dbname);
			$field = extra_field::getInstance();
			$listField[] = $field->list_all_field(2, $item->category_id, $dbname);
		}

		return implode('', $listField);
	}

	/**
	 * Method to store category.
	 *
	 * @param   object  $data  The object category data.
	 *
	 * @return  boolen
	 *
	 * @since   2.0.0.2
	 */
	public function store($data)
	{
		$row = $this->getTable();

		if (!$row->bind($data))
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		$db = $this->getDBO();
		$filename = "";

		if (isset($data['image_delete']))
		{
			unlink(REDSHOP_FRONT_IMAGES_RELPATH . 'category/thumb/' . $data['old_image']);
			unlink(REDSHOP_FRONT_IMAGES_RELPATH . 'category/' . $data['old_image']);

			$fields = array(
				$db->qn('category_thumb_image') . ' = ""',
				$db->qn('category_full_image') . ' = ""'
			);

			$conditions = array(
				$db->qn('category_id') . ' = ' . $db->q((int) $row->category_id)
			);

			$query = $db->getQuery(true)
				->update($db->qn('#__redshop_category'))
				->set($fields)
				->where($conditions);
			$db->setQuery($query)->execute();
		}

		if (!empty($data['category_full_image']['name']))
		{
			// Make the filename unique
			$filename = RedShopHelperImages::cleanFileName($data['category_full_image']['name']);
			$newwidth = Redshop::getConfig()->get('THUMB_WIDTH');
			$newheight = Redshop::getConfig()->get('THUMB_HEIGHT');

			$row->category_full_image = $filename;
			$row->category_thumb_image = $filename;

			// Get extension of the file
			$filetype = JFile::getExt($data['category_full_image']['name']);

			$src = $data['category_full_image']['tmp_name'];

			// Specific path of the file
			$dest = REDSHOP_FRONT_IMAGES_RELPATH . 'category/' . $filename;

			JFile::upload($src, $dest);
		}
		else
		{
			if (isset($data['category_image']) && $data['category_image'] != null)
			{
				$image_split = explode('/', $data['category_image']);

				// Make the filename unique
				$filename = RedShopHelperImages::cleanFileName($image_split[count($image_split) - 1]);
				$row->category_full_image = $filename;
				$row->category_thumb_image = $filename;

				// Image Upload
				$newwidth = Redshop::getConfig()->get('THUMB_WIDTH');
				$newheight = Redshop::getConfig()->get('THUMB_HEIGHT');

				$src = JPATH_ROOT . '/' . $data['category_image'];
				$dest = REDSHOP_FRONT_IMAGES_RELPATH . 'category/' . $filename;

				copy($src, $dest);
			}
		}

		if (isset($data['image_back_delete']))
		{
			unlink(REDSHOP_FRONT_IMAGES_RELPATH . 'category/thumb/' . $data['old_back_image']);
			unlink(REDSHOP_FRONT_IMAGES_RELPATH . 'category/' . $data['old_back_image']);

			$fields = array(
				$db->qn('category_back_full_image') . ' = ""'
			);

			$conditions = array(
				$db->qn('category_id') . ' = ' . $db->q((int) $row->category_id)
			);

			$query = $db->getQuery(true)
				->update($db->qn('#__redshop_category'))
				->set($fields)
				->where($conditions);
			$db->setQuery($query)->execute();
		}

		if (!empty($data['category_back_full_image']['name']))
		{
			// Make the filename unique
			$filename = RedShopHelperImages::cleanFileName($data['category_back_full_image']['name']);
			$row->category_back_full_image = $filename;

			// Get extension of the file
			$filetype = JFile::getExt($data['category_back_full_image']['name']);

			$src = $data['category_back_full_image']['tmp_name'];

			// Specific path of the file
			$dest = REDSHOP_FRONT_IMAGES_RELPATH . 'category/' . $filename;

			JFile::upload($src, $dest);
		}

		// Upload back image end
		if (!$row->store())
		{
			$this->setError($this->_db->getErrorMsg());

			return false;
		}

		if (!$data['category_id'])
		{
			$newCatId = $this->_db->insertid();
			$columns  = array('category_parent_id', 'category_child_id');
			$values   = array($db->q((int) $data['category_parent_id']), $db->q((int) $newCatId));

			$query = $db->getQuery(true)
				->insert($db->quoteName('#__redshop_category_xref'))
				->columns($db->quoteName($columns))
				->values(implode(',', $values));

			$db->setQuery($query)->execute();
		}
		else
		{
			$newCatId = $data['category_id'];

			$fields = array(
				$db->qn('category_parent_id') . ' = ' . $db->q((int) $data['category_parent_id'])
			);

			$conditions = array(
				$db->qn('category_child_id') . ' = ' . $db->q((int) $newCatId)
			);

			$query = $db->getQuery(true)
				->update($db->qn('#__redshop_category_xref'))
				->set($fields)
				->where($conditions);
			$db->setQuery($query)->execute();

			// Sheking for the image at the updation time
			if (empty($data['category_full_image']['name']))
			{
				@unlink(REDSHOP_FRONT_IMAGES_RELPATH . 'category/thumb/' . $data['old_image']);
				@unlink(REDSHOP_FRONT_IMAGES_RELPATH . 'category/' . $data['old_image']);
			}
		}

		// Extra Field Data Saved
		$field = extra_field::getInstance();
		$field->extra_field_save($data, 2, $newCatId);

		// Start Accessory Product
		if (count($data['product_accessory']) > 0 && is_array($data['product_accessory']))
		{
			$data['product_accessory'] = array_merge(array(), $data['product_accessory']);

			$product_category = new product_category;
			$product_list = $product_category->getCategoryProductList($newCatId);

			for ($p = 0, $pn = count($product_list); $p < $pn; $p++)
			{
				$product_id = $product_list[$p]->id;

				for ($a = 0; $a < count($data['product_accessory']); $a++)
				{
					$acc = $data['product_accessory'][$a];

					$accessory_id = $product_category->CheckAccessoryExists($product_id, $acc['child_product_id']);

					if ($product_id != $acc['child_product_id'])
					{
						$accdetail = $this->getTable('accessory_detail');

						$accdetail->accessory_id = $accessory_id;
						$accdetail->category_id = $newCatId;
						$accdetail->product_id = $product_id;
						$accdetail->child_product_id = $acc['child_product_id'];
						$accdetail->accessory_price = $acc['accessory_price'];
						$accdetail->oprand = $acc['oprand'];
						$accdetail->ordering = $acc['ordering'];
						$accdetail->setdefault_selected = (isset($acc['setdefault_selected']) && $acc['setdefault_selected'] == 1) ? 1 : 0;

						if (!$accdetail->store())
						{
							$this->setError($this->_db->getErrorMsg());

							return false;
						}
					}
				}
			}
		}

		// End Accessory Product insert
		return $row;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   2.0.0.2
	 */
	public function delete(&$pks)
	{
		$noError = true;
		$cid = $pks;
		$db = $this->getDBO();

		for ($i = 0, $in = count($cid); $i < $in; $i++)
		{
			$query = $db->getQuery(true)
				->select('COUNT(*) AS ctotal')
				->select($db->qn('c.category_name'))
				->from($db->qn('#__redshop_category_xref', 'cx'))
				->leftJoin($db->qn('#__redshop_category', 'c') . ' ON ' . $db->qn('c.category_id') . ' = ' . $db->q((int) $cid[$i]))
				->where($db->qn('cx.category_parent_id') . ' = ' . $db->q((int) $cid[$i]));

			$childs = $db->setQuery($query)->loadObject();

			if ($childs->ctotal > 0)
			{
				$noError = false;
				$errorMSG = sprintf(JText::_('COM_REDSHOP_CATEGORY_PARENT_ERROR_MSG'), $childs->category_name, $cid[$i]);
				$this->setError($errorMSG);
				break;
			}

			$query = $db->getQuery(true)
				->select($db->qn('category_thumb_image'))
				->select($db->qn('category_full_image'))
				->from($db->qn('#__redshop_category'))
				->where($db->qn('category_id') . ' = ' . $db->q((int) $cid[$i]));

			$catImages = $db->setQuery($query)->loadObject();

			$catThumbImage = $catImages->category_thumb_image;
			$catFullImage = $catImages->category_full_image;

			$thumbPath = REDSHOP_FRONT_IMAGES_RELPATH . 'category/thumb/' . $catThumbImage;
			$fullImagePath = REDSHOP_FRONT_IMAGES_RELPATH . 'category/' . $catFullImage;

			if (file_exists($thumbPath))
			{
				@unlink($thumbPath);
			}

			if (file_exists($fullImagePath))
			{
				@unlink($fullImagePath);
			}

			$conditions = array(
				$db->qn('category_id') . ' = ' . $db->q((int) $cid[$i])
			);

			$conditionChild = array(
				$db->qn('category_child_id') . ' = ' . $db->q((int) $cid[$i])
			);

			$query = $db->getQuery(true)
				->delete($db->qn('#__redshop_product_category_xref'))
				->where($conditions);
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->delete($db->qn('#__redshop_category_xref'))
				->where($conditionChild);
			$db->setQuery($query)->execute();

			$query = $db->getQuery(true)
				->delete($db->qn('#__redshop_category'))
				->where($conditions);
			$db->setQuery($query)->execute();
		}

		return $noError;
	}

	/**
	 * Method to get categories.
	 *
	 * @since   2.0.0.2
	 */
	public function getcategories()
	{
		$db = $this->getDBO();
		$query = $db->getQuery(true)
			->select($db->qn('category_id', 'value'))
			->select($db->qn('category_name', 'text'))
			->from($db->qn('#__redshop_category'))
			->where($db->qn('published') . ' = 1');

		return $db->setQuery($query)->loadObjectlist();
	}

	/**
	 * Saves the manually set order of records.
	 *
	 * @param   array    $pks    An array of primary key ids.
	 * @param   integer  $order  +1 or -1
	 *
	 * @return  mixed
	 *
	 * @since   12.2
	 */
	public function saveorder($pks = null, $order = null)
	{
		$row = $this->getTable();
		$groupings = array();

		// Update ordering values
		for ($i = 0, $in = count($pks); $i < $in; $i++)
		{
			$row->load((int) $pks[$i]);

			// Track categories
			$groupings[] = $row->category_id;

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];

				if (!$row->store())
				{
					$this->setError($this->_db->getErrorMsg());

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Method to update order.
	 *
	 * @param   string   $oprand  oprand.
	 * @param   int      $catId   category id
	 *
	 * @return  boolen
	 *
	 * @since   2.0.0.2
	 */
	public function updateorder($oprand, $catId = 0)
	{
		$db = $this->getDBO();

		$fields = array(
			$db->qn('ordering') . ' = ' . $db->q('ordering' . $oprand . '1')
		);

		$conditions = array(
				$db->qn('ordering') . ' != 0'
			);

		$query = $db->getQuery(true)
			->update($db->qn('#__redshop_category'))
			->set($fields);

		if ($catId)
		{
			$query->where($conditions);
		}

		return $db->setQuery($query)->execute();
	}

	/**
	 * Method to order up.
	 *
	 * @param   array   $cid  category id list.
	 *
	 * @return  boolen
	 *
	 * @since   2.0.0.2
	 */
	public function orderUp($cid)
	{
		$cid = $cid[0];
		$db = $this->getDBO();
		$query = $db->getQuery(true)
			->select($db->qn('c.ordering'))
			->select($db->qn('cx.category_parent_id'))
			->from($db->qn('#__redshop_category', 'c'))
			->leftJoin($db->qn('#__redshop_category_xref', 'cx') . ' ON ' . $db->qn('cx.category_child_id') . ' = ' . $db->qn('c.category_id'))
			->where($db->qn('c.category_id') . ' = ' . $db->q((int) $cid));

		$cat = $db->setQuery($query)->loadObject();
		$currentPos = $cat->ordering;
		$categoryParentId = $cat->category_parent_id;

		$query = $db->getQuery(true)
			->select($db->qn('c.ordering'))
			->select($db->qn('c.category_id'))
			->from($db->qn('#__redshop_category', 'c'))
			->leftJoin($db->qn('#__redshop_category_xref', 'cx') . ' ON ' . $db->qn('cx.category_child_id') . ' = ' . $db->qn('c.category_id'))
			->where($db->qn('category_parent_id') . ' = ' . $db->q((int) $categoryParentId))
			->where($db->qn('ordering') . ' = ' . $db->q((int) ($currentPos - 1)));

		$cat = $db->setQuery($query)->loadObject();
		$pred = $cat->category_id;

		$mOrder = $this->getmaxminOrder('min');

		if ($currentPos > $mOrder)
		{
			$fields = array(
				$db->qn('ordering') . ' = ' . $db->q('ordering - 1')
			);

			$conditions = array(
					$db->qn('category_id') . ' = ' . $db->q((int) $cid)
				);

			$query = $db->getQuery(true)
				->update($db->qn('#__redshop_category'))
				->set($fields);
			$db->setQuery($query)->execute();

			$fields = array(
				$db->qn('ordering') . ' = ' . $db->q('ordering + 1')
			);

			$conditions = array(
					$db->qn('category_id') . ' = ' . $db->q((int) $pred)
				);

			$query = $db->getQuery(true)
				->update($db->qn('#__redshop_category'))
				->set($fields);
			$db->setQuery($query)->execute();
		}
	}

	/**
	 * Method to order down.
	 *
	 * @param   array   $cid  category id list.
	 *
	 * @return  boolen
	 *
	 * @since   2.0.0.2
	 */
	public function orderDown($cid)
	{
		$cid = $cid[0];
		$db = $this->getDBO();
		$query = $db->getQuery(true)
			->select($db->qn('c.ordering'))
			->select($db->qn('cx.category_parent_id'))
			->from($db->qn('#__redshop_category', 'c'))
			->leftJoin($db->qn('#__redshop_category_xref', 'cx') . ' ON ' . $db->qn('cx.category_child_id') . ' = ' . $db->qn('c.category_id'))
			->where($db->qn('c.category_id') . ' = ' . $db->q((int) $cid));

		$cat = $db->setQuery($query)->loadObject();
		$currentPos = $cat->ordering;
		$categoryParentId = $cat->category_parent_id;

		$query = $db->getQuery(true)
			->select($db->qn('c.ordering'))
			->select($db->qn('c.category_id'))
			->from($db->qn('#__redshop_category', 'c'))
			->leftJoin($db->qn('#__redshop_category_xref', 'cx') . ' ON ' . $db->qn('cx.category_child_id') . ' = ' . $db->qn('c.category_id'))
			->where($db->qn('category_parent_id') . ' = ' . $db->q((int) $categoryParentId))
			->where($db->qn('ordering') . ' = ' . $db->q((int) ($currentPos + 1)));
		$cat = $db->setQuery($query)->loadObject();
		$succ = $cat->category_id;

		$mOrder = $this->getmaxminOrder('max');

		if ($currentPos < $mOrder)
		{
			$fields = array(
				$db->qn('ordering') . ' = ' . $db->q('ordering + 1')
			);

			$conditions = array(
					$db->qn('category_id') . ' = ' . $db->q((int) $cid)
				);

			$query = $db->getQuery(true)
				->update($db->qn('#__redshop_category'))
				->set($fields);
			$db->setQuery($query)->execute();

			$fields = array(
				$db->qn('ordering') . ' = ' . $db->q('ordering - 1')
			);

			$conditions = array(
					$db->qn('category_id') . ' = ' . $db->q((int) $succ)
				);

			$query = $db->getQuery(true)
				->update($db->qn('#__redshop_category'))
				->set($fields);
			$db->setQuery($query)->execute();
		}
	}

	/**
	 * Method to get max min ordering.
	 *
	 * @param   string   $type  type of order min/max.
	 *
	 * @return  boolen
	 *
	 * @since   2.0.0.2
	 */
	public function getmaxminOrder($type)
	{
		$db = $this->getDBO();
		$query = $db->getQuery(true)
			->select($db->qn($type . '(ordering)', 'morder'))
			->from($db->qn('#__redshop_category'));

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Method to get product compare template.
	 *
	 * @since   2.0.0.2
	 */
	public function getProductCompareTemplate()
	{
		$db = $this->getDBO();
		$query = $db->getQuery(true)
			->select($db->qn('template_section', 'text'))
			->select($db->qn('template_id', 'value'))
			->from($db->qn('#__redshop_template'))
			->where($db->qn('published') . ' = 1')
			->where($db->qn('template_section') . ' = ' . $db->q('compare_product'));

		return $db->setQuery($query)->loadObjectList();
	}

	/**
	 * Method to copy.
	 *
	 * @param   array   $cid  category id list.
	 *
	 * @return  boolen
	 *
	 * @since   2.0.0.2
	 */
	public function copy($cid = array())
	{
		if (count($cid))
		{
			$db = $this->getDBO();
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__redshop_category'))
				->where($db->qn('category_id') . ' IN (' . implode(',', $cid) . ')');
			$copydata = $db->setQuery($query)->loadObjectList();

			for ($i = 0, $in = count($copydata); $i < $in; $i++)
			{
				$query = $db->getQuery(true)
					->select($db->qn('category_parent_id'))
					->from($db->qn('#__redshop_category_xref'))
					->where($db->qn('category_child_id') . ' = ' . $db->q((int) $copydata[$i]->category_id));

				$categoryParentId = $db->setQuery($query)->loadResult();

				$post = array();
				$post['category_id'] = 0;
				$post['category_name'] = $this->renameToUniqueValue('category_name', $copydata[$i]->category_name);
				$post['category_short_description'] = $copydata[$i]->category_short_description;
				$post['category_description'] = $copydata[$i]->category_description;
				$post['category_template'] = $copydata[$i]->category_template;
				$post['category_more_template'] = $copydata[$i]->category_more_template;
				$post['products_per_page'] = $copydata[$i]->products_per_page;
				$post['category_full_image'] = $this->renameToUniqueValue('category_full_image', $copydata[$i]->category_full_image, 'dash');
				$post['category_thumb_image'] = $this->renameToUniqueValue('category_thumb_image', $copydata[$i]->category_thumb_image, 'dash');
				$post['metakey'] = $copydata[$i]->metakey;
				$post['metadesc'] = $copydata[$i]->metadesc;
				$post['metalanguage_setting'] = $copydata[$i]->metalanguage_setting;
				$post['metarobot_info'] = $copydata[$i]->metarobot_info;
				$post['pagetitle'] = $copydata[$i]->pagetitle;
				$post['pageheading'] = $copydata[$i]->pageheading;
				$post['sef_url'] = $copydata[$i]->sef_url;
				$post['published'] = $copydata[$i]->published;
				$post['category_pdate'] = date("Y-m-d h:i:s");
				$post['ordering'] = count($copydata) + $i + 1;

				$post['category_parent_id'] = $category_parent_id;

				$src = REDSHOP_FRONT_IMAGES_RELPATH . 'category/' . $copydata[$i]->category_full_image;
				$dest = REDSHOP_FRONT_IMAGES_RELPATH . 'category/' . $post['category_full_image'];

				if (is_file($src))
				{
					JFile::upload($src, $dest);
				}

				$this->store($post);
			}
		}

		return true;
	}
}
