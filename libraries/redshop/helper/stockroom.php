<?php
/**
 * @package     RedSHOP.Library
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2008 - 2016 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Class Redshop Helper Stock Room
 *
 * @since  1.5
 */
class RedshopHelperStockroom
{
	/**
	 * Check already notified user
	 *
	 * @param   int  $userId         User id
	 * @param   int  $productId      Product id
	 * @param   int  $propertyId     Property id
	 * @param   int  $subPropertyId  Sub property id
	 *
	 * @return mixed
	 */
	public static function isAlreadyNotifiedUser($userId, $productId, $propertyId, $subPropertyId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from($db->qn('#__redshop_notifystock_users'))
			->where('product_id = ' . (int) $productId)
			->where('property_id = ' . (int) $propertyId)
			->where('subproperty_id = ' . (int) $subPropertyId)
			->where('user_id =' . (int) $userId)
			->where('notification_status = 0');

		return $db->setQuery($query)->loadResult();
	}

	/**
	 * Get stockroom detail
	 *
	 * @param   int  $stockroomId  stockroom id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getStockroomDetail($stockroomId = 0)
	{
		$list = array();

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$db = JFactory::getDBO();
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__redshop_stockroom'));

			if (!empty($stockroomId))
			{
				$query->where($db->qn('stockroom_id') . ' = ' . $db->q((int) $stockroomId));
			}

			$list = $db->setQuery($query)->loadObjectList();
		}

		return $list;
	}

	/**
	 * Check is stock exists
	 *
	 * @param   int  $sectionId    Section id
	 * @param   int  $section      Section
	 * @param   int  $stockroomId  Stockroom id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function isStockExists($sectionId = 0, $section = "product", $stockroomId = 0)
	{
		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$stock = self::getStockAmountwithReserve($sectionId, $section, $stockroomId);

			if ($stock > 0)
			{
				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * Check is attribute stock exists
	 *
	 * @param   int  $productId  Product id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function isAttributeStockExists($productId)
	{
		$isStockExists = false;
		$productHelper = productHelper::getInstance();
		$property = $productHelper->getAttibuteProperty(0, 0, $productId);

		for ($att_j = 0; $att_j < count($property); $att_j++)
		{
			$isSubpropertyStock = false;
			$subProperty = $productHelper->getAttibuteSubProperty(0, $property[$att_j]->property_id);

			for ($sub_j = 0; $sub_j < count($subProperty); $sub_j++)
			{
				$isSubpropertyStock = self::isStockExists($subProperty[$sub_j]->subattribute_color_id, 'subproperty');

				if ($isSubpropertyStock)
				{
					$isStockExists = $isSubpropertyStock;

					return $isStockExists;
				}
			}

			if ($isSubpropertyStock)
			{
				return $isStockExists;
			}
			else
			{
				$isPropertystock = self::isStockExists($property[$att_j]->property_id, "property");

				if ($isPropertystock)
				{
					$isStockExists = $isPropertystock;

					return $isStockExists;
				}
			}
		}

		return $isStockExists;
	}

	/**
	 * Check is pre-order stock exists
	 *
	 * @param   int  $sectionId    Section id
	 * @param   int  $section      Section
	 * @param   int  $stockroomId  Stockroom id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function isPreorderStockExists($sectionId = 0, $section = "product", $stockroomId = 0)
	{
		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$stock = self::getPreorderStockAmountwithReserve($sectionId, $section, $stockroomId);

			if ($stock > 0)
			{
				return true;
			}

			return false;
		}

		return true;
	}

	/**
	 * Check is attribute pre-order stock exists
	 *
	 * @param   int  $productId  Product id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function isAttributePreorderStockExists($productId)
	{
		$productHelper = productHelper::getInstance();
		$property = $productHelper->getAttibuteProperty(0, 0, $productId);

		for ($att_j = 0; $att_j < count($property); $att_j++)
		{
			$isSubpropertyStock = false;
			$subProperty = $productHelper->getAttibuteSubProperty(0, $property[$att_j]->property_id);

			for ($sub_j = 0; $sub_j < count($subProperty); $sub_j++)
			{
				$isSubpropertyStock = self::isPreorderStockExists($subProperty[$sub_j]->subattribute_color_id, 'subproperty');

				if ($isSubpropertyStock)
				{
					$isPreorderStockExists = $isSubpropertyStock;

					return $isPreorderStockExists;
				}
			}

			if ($isSubpropertyStock)
			{
				return $isPreorderStockExists;
			}
			else
			{
				$isPropertystock = self::isPreorderStockExists($property[$att_j]->property_id, "property");

				if ($isPropertystock)
				{
					$isPreorderStockExists = $isPropertystock;

					return $isPreorderStockExists;
				}
			}
		}

		return $isPreorderStockExists;
	}

	/**
	 * Get Stockroom Total amount
	 *
	 * @param   int  $sectionId    Section id
	 * @param   int  $section      Section
	 * @param   int  $stockroomId  Stockroom id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getStockroomTotalAmount($sectionId = 0, $section = "product", $stockroomId = 0)
	{
		$quantity = 1;

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$quantity = self::getStockAmountwithReserve($sectionId, $section, $stockroomId);

			$reserveQuantity = self::getReservedStock($sectionId, $section);
			$quantity = $quantity - $reserveQuantity;

			if ($quantity < 0)
			{
				$quantity = 0;
			}
		}

		return $quantity;
	}

	/**
	 * Get pre-order stockroom total amount
	 *
	 * @param   int  $sectionId    Section id
	 * @param   int  $section      Section
	 * @param   int  $stockroomId  Stockroom id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getPreorderStockroomTotalAmount($sectionId = 0, $section = "product", $stockroomId = 0)
	{
		$quantity = 1;

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$quantity = self::getPreorderStockAmountwithReserve($sectionId, $section, $stockroomId);

			$reserveQuantity = self::getReservedStock($sectionId, $section);
			$quantity = $quantity - $reserveQuantity;

			if ($quantity < 0)
			{
				$quantity = 0;
			}
		}

		return $quantity;
	}

	/**
	 * Get Stock Amount with Reserve
	 *
	 * @param   int     $sectionId    Section id
	 * @param   string  $section      Section
	 * @param   int     $stockroomId  Stockroom id
	 *
	 * @return int|mixed
	 */
	public static function getStockAmountwithReserve($sectionId = 0, $section = 'product', $stockroomId = 0)
	{
		$quantity = 1;
		$productHelper = productHelper::getInstance();

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			if ($section == 'product' && $stockroomId == 0 && $sectionId)
			{
				$sectionId = explode(',', $sectionId);
				JArrayHelper::toInteger($sectionId);
				$quantity = 0;

				foreach ($sectionId as $item)
				{
					$productData = Redshop::product((int) $item);

					if (isset($productData->sum_quanity))
					{
						$quantity += $productData->sum_quanity;
					}
				}
			}
			else
			{
				$table = 'product';
				$db = JFactory::getDbo();

				if ($section != 'product')
				{
					$table = 'product_attribute';
				}

				$query = $db->getQuery(true)
					->select('SUM(x.quantity)')
					->from($db->qn('#__redshop_' . $table . '_stockroom_xref', 'x'))
					->leftJoin($db->qn('#__redshop_stockroom', 's') . ' ON s.stockroom_id = x.stockroom_id')
					->where('x.quantity >= 0');

				if ($sectionId != 0)
				{
					$sectionId = explode(',', $sectionId);
					JArrayHelper::toInteger($sectionId);

					if ($section != 'product')
					{
						$query->where('x.section = ' . $db->quote($section))
							->where('x.section_id IN (' . implode(',', $sectionId) . ')');
					}
					else
					{
						$query->where('x.product_id IN (' . implode(',', $sectionId) . ')');
					}
				}

				if ($stockroomId != 0)
				{
					$query->where('x.stockroom_id = ' . (int) $stockroomId);
				}

				$db->setQuery($query);
				$quantity = $db->loadResult();
			}

			if ($quantity < 0)
			{
				$quantity = 0;
			}
		}

		if ($quantity == null)
		{
			$quantity = (Redshop::getConfig()->get('USE_BLANK_AS_INFINITE')) ? 1000000000 : 0;
		}

		return $quantity;
	}

	/**
	 * Get pre-order stockroom amount with reserve
	 *
	 * @param   int  $sectionId    Section id
	 * @param   int  $section      Section
	 * @param   int  $stockroomId  Stockroom id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getPreorderStockAmountwithReserve($sectionId = 0, $section = "product", $stockroomId = 0)
	{
		$quantity = 1;

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$table = "#__redshop_product_stockroom_xref";

			if ($section != "product")
			{
				$table = "#__redshop_product_attribute_stockroom_xref";
			}

			$db = JFactory::getDBO();
			$query = $db->query(true)
				->select('SUM(x.preorder_stock) AS preorder_stock')
				->select('SUM(x.ordered_preorder) AS ordered_preorder')
				->from($db->qn($table, 'x'))
				->leftJoin($db->qn('#__redshop_stockroom', 's') . ' ON ' . $db->qn('x.stockroom_id') . ' = ' . $db->qn('s.stockroom_id'))
				->where($db->qn('x.quantity') . ' >= 0')
				->order($db->qn('s.min_del_time'));

			if ($sectionId != 0)
			{
				// Sanitize ids
				$sectionId = explode(',', $sectionId);
				JArrayHelper::toInteger($sectionId);

				if ($section != "product")
				{
					$query->where($db->qn('x.section') . ' = ' . $db->q($section))
						->where($db->qn('x.section_id') . ' IN (' . implode(',', $sectionId) . ')');
				}
				else
				{
					$query->where($db->qn('x.product_id') . ' IN (' . implode(',', $sectionId) . ')');
				}
			}

			if ($stockroomId != 0)
			{
				$query->where($db->qn('x.stockroom_id') . ' = ' . $db->q((int) $stockroomId));
			}

			$preOrderStock = $db->setQuery($query)->loadObjectList();

			if ($preOrderStock[0]->ordered_preorder == $preOrderStock[0]->preorder_stock
				|| $preOrderStock[0]->ordered_preorder > $preOrderStock[0]->preorder_stock)
			{
				$quantity = 0;
			}
			else
			{
				$quantity = $preOrderStock[0]->preorder_stock - $preOrderStock[0]->ordered_preorder;
			}
		}

		return $quantity;
	}

	/**
	 * Get stockroom amount detail list
	 *
	 * @param   int  $sectionId    Section id
	 * @param   int  $section      Section
	 * @param   int  $stockroomId  Stockroom id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getStockroomAmountDetailList($sectionId = 0, $section = "product", $stockroomId = 0)
	{
		$list = array();

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$table = "#__redshop_product_stockroom_xref";

			if ($section != "product")
			{
				$table = "#__redshop_product_attribute_stockroom_xref";
			}

			$db = JFactory::getDbo();
			$query = $db->query(true)
				->select('*')
				->from($db->qn($table, 'x'))
				->leftJoin($db->qn('#__redshop_stockroom', 's') . ' ON ' . $db->qn('x.stockroom_id') . ' = ' . $db->qn('s.stockroom_id'))
				->where($db->qn('x.quantity') . ' > 0')
				->order($db->qn('s.min_del_time'));

			if ($sectionId != 0)
			{
				if ($section != "product")
				{
					$query->where($db->qn('x.section') . ' = ' . $db->q($section))
						->where($db->qn('x.section_id') . ' = ' . $db->q((int) $sectionId));
				}
				else
				{
					$query->where($db->qn('x.product_id') . ' =' . $db->q((int) $sectionId));
				}
			}

			if ($stockroomId != 0)
			{
				$query->where($db->qn('x.stockroom_id') . ' = ' . $db->q((int) $stockroomId));
			}

			$list = $db->setQuery($query)->loadObjectList();
		}

		return $list;
	}

	/**
	 * Get pre-order stockroom amount detail list
	 *
	 * @param   int  $sectionId    Section id
	 * @param   int  $section      Section
	 * @param   int  $stockroomId  Stockroom id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getPreorderStockroomAmountDetailList($sectionId = 0, $section = "product", $stockroomId = 0)
	{
		$list = array();

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$table = "#__redshop_product_stockroom_xref";

			if ($section != "product")
			{
				$table = "#__redshop_product_attribute_stockroom_xref";
			}

			$db = JFactory::getDbo();
			$query = $db->query(true)
				->select('*')
				->from($db->qn($table, 'x'))
				->leftJoin($db->qn('#__redshop_stockroom', 's') . ' ON ' . $db->qn('x.stockroom_id') . ' = ' . $db->qn('s.stockroom_id'))
				->where($db->qn('x.preorder_stock') . ' >= ' . $db->qn('x.ordered_preorder'))
				->order($db->qn('s.min_del_time'));

			if ($sectionId != 0)
			{
				if ($section != "product")
				{
					$query->where($db->qn('x.section') . ' = ' . $db->q($section))
						->where($db->qn('x.section_id') . ' = ' . $db->q((int) $sectionId));
				}
				else
				{
					$query->where($db->qn('x.product_id') . ' =' . $db->q((int) $sectionId));
				}
			}

			if ($stockroomId != 0)
			{
				$query->where($db->qn('x.stockroom_id') . ' = ' . $db->q((int) $stockroomId));
			}

			$list = $db->setQuery($query)->loadObjectList();
		}

		return $list;
	}

	/**
	 * Update stockroom quantity
	 *
	 * @param   int  $sectionId  Section id
	 * @param   int  $quantity   Stockroom quantity
	 * @param   int  $section    Section
	 * @param   int  $productId  Product id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function updateStockroomQuantity($sectionId = 0, $quantity = 0, $section = "product", $productId = 0)
	{
		$affectedRow = array();
		$stockroomQuantity = array();

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$list = self::getStockroomAmountDetailList($sectionId, $section);

			for ($i = 0, $in = count($list); $i < $in; $i++)
			{
				if ($list[$i]->quantity < $quantity)
				{
					$quantity = $quantity - $list[$i]->quantity;
					$remainingQuantity = $list[$i]->quantity;
				}
				else
				{
					$remainingQuantity = $quantity;
					$quantity -= $remainingQuantity;
				}

				if ($remainingQuantity > 0)
				{
					self::updateStockAmount($sectionId, $remainingQuantity, $list[$i]->stockroom_id, $section);
					$affectedRow[] = $list[$i]->stockroom_id;
					$stockroomQuantity[] = $remainingQuantity;
				}

				$stockroomDetail = self::getStockroomAmountDetailList($sectionId, $section, $list[$i]->stockroom_id);
				$remaining = $stockroomDetail[0]->quantity - $quantity;

				if (Redshop::getConfig()->get('ENABLE_STOCKROOM_NOTIFICATION') == 1 && $remaining <= Redshop::getConfig()->get('DEFAULT_STOCKROOM_BELOW_AMOUNT_NUMBER'))
				{
					$dispatcher = JDispatcher::getInstance();
					JPluginHelper::importPlugin('redshop_alert');
					$productId = ($section == "product") ? $sectionId : $productId;
					$productData = Redshop::product((int) $productId);

					$message = JText::sprintf(
						'COM_REDSHOP_ALERT_STOCKROOM_BELOW_AMOUNT_NUMBER',
						$productData->product_id,
						$productData->product_name,
						$productData->product_number,
						$remaining,
						$stockroomDetail[0]->stockroom_name
					);

					$dispatcher->trigger('storeAlert', array($message));
					$dispatcher->trigger('sendEmail', array($message));
				}
			}

			// For preorder stock
			if ($quantity > 0)
			{
				$preorderList = self::getPreorderStockroomAmountDetailList($sectionId, $section);

				if ($section == "product")
				{
					$productData = Redshop::product((int) $sectionId);
				}
				else
				{
					$productData = Redshop::product((int) $productId);
				}

				if ($productData->preorder == "yes" || ($productData->preorder == "global" && Redshop::getConfig()->get('ALLOW_PRE_ORDER'))
					|| ($productData->preorder == "" && Redshop::getConfig()->get('ALLOW_PRE_ORDER')))
				{
					for ($i = 0, $in = count($preorderList); $i < $in; $i++)
					{
						if ($preorderList[$i]->preorder_stock < $quantity)
						{
							$quantity = $quantity - $preorderList[$i]->preorder_stock;
							$remainingQuantity = $preorderList[$i]->preorder_stock;
						}
						else
						{
							$remainingQuantity = $quantity;
							$quantity -= $remainingQuantity;
						}

						if ($remainingQuantity > 0)
						{
							self::updatePreorderStockAmount($sectionId, $remainingQuantity, $preorderList[$i]->stockroom_id, $section);
						}
					}
				}
			}
		}

		$list = implode(",", $affectedRow);
		$stockroomQuantityList = implode(",", $stockroomQuantity);
		$resultArray = array();
		$resultArray['stockroom_list'] = $list;
		$resultArray['stockroom_quantity_list'] = $stockroomQuantityList;

		return $resultArray;
	}

	/**
	 * Update stockroom amount
	 *
	 * @param   int  $sectionId    Section id
	 * @param   int  $quantity     Stockroom quantity
	 * @param   int  $stockroomId  Stockroom id
	 * @param   int  $section      Section
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function updateStockAmount($sectionId = 0, $quantity = 0, $stockroomId = 0, $section = "product")
	{
		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$table = "#__redshop_product_stockroom_xref";

			if ($section != "product")
			{
				$table = "#__redshop_product_attribute_stockroom_xref";
			}

			$db = JFactory::getDbo();

			if ($sectionId != 0)
			{
				$fields = array(
					$db->qn('quantity') . ' = ' . $db->q('quantity - ' . (int) $quantity)
				);

				$conditions = array(
					$db->qn('stockroom_id') . ' = ' . $db->q((int) $stockroomId),
					$db->qn('quantity') . ' > 0'
				);

				if ($section != "product")
				{
					$conditions[] = $db->qn('section') . ' = ' . $db->q($section);
					$conditions[] = $db->qn('section_id') . ' = ' . $db->q((int) $sectionId);
				}
				else
				{
					$conditions[] = $db->qn('product_id') . ' = ' . $db->q((int) $sectionId);
				}

				$query = $db->getQuery(true)
					->update($db->qn($table))
					->set($fields)
					->where($conditions);

				$db->setQuery($query)->execute();
			}
		}

		return true;
	}

	/**
	 * Update pre-order stock amount
	 *
	 * @param   int  $sectionId    Section id
	 * @param   int  $quantity     Stockroom quantity
	 * @param   int  $stockroomId  Stockroom id
	 * @param   int  $section      Section
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function updatePreorderStockAmount($sectionId = 0, $quantity = 0, $stockroomId = 0, $section = "product")
	{
		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1 && $sectionId != 0 && trim($sectionId) != "")
		{
			$table = "#__redshop_product_stockroom_xref";

			if ($section != "product")
			{
				$table = "#__redshop_product_attribute_stockroom_xref";
			}

			$db = JFactory::getDbo();

			$fields = array(
				$db->qn('ordered_preorder') . ' = ' . $db->q('ordered_preorder + ' . (int) $quantity)
			);

			$conditions = array(
				$db->qn('stockroom_id') . ' = ' . $db->q((int) $stockroomId)
			);

			if ($section != "product")
			{
				$conditions[] = $db->qn('section') . ' = ' . $db->q($section);
				$conditions[] = $db->qn('section_id') . ' = ' . $db->q((int) $sectionId);
			}
			else
			{
				$conditions[] = $db->qn('product_id') . ' = ' . $db->q((int) $sectionId);
			}

			$query = $db->getQuery(true)
				->update($db->qn($table))
				->set($fields)
				->where($conditions);

			$db->setQuery($query)->execute();
		}

		return true;
	}

	/**
	 * Manage stock amount
	 *
	 * @param   int  $sectionId    Section id
	 * @param   int  $quantity     Stockroom quantity
	 * @param   int  $stockroomId  Stockroom id
	 * @param   int  $section      Section
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function manageStockAmount($sectionId = 0, $quantity = 0, $stockroomId = 0, $section = "product")
	{
		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			$table = "#__redshop_product_stockroom_xref";

			if ($section != "product")
			{
				$table = "#__redshop_product_attribute_stockroom_xref";
			}

			$db = JFactory::getDbo();
			$conditions = array();

			if ($sectionId != 0 && trim($sectionId) != "")
			{
				if ($section != "product")
				{
					$conditions[] = $db->qn('section') . ' = ' . $db->q($section);
					$conditions[] = $db->qn('section_id') . ' = ' . $db->q((int) $sectionId);
				}
				else
				{
					$conditions[] = $db->qn('product_id') . ' = ' . $db->q((int) $sectionId);
				}
			}

			$stockId = explode(",", $stockroomId);
			$stockQty = explode(",", $quantity);

			for ($i = 0, $in = count($stockId); $i < $in; $i++)
			{
				if ($stockId[$i] != "" && $sectionId != "" && $sectionId != 0)
				{
					$fields = array(
						$db->qn('quantity') . ' = ' . $db->q('quantity + ' . (int) $stockQty[$i])
					);

					$conditions[] = $db->qn('stockroom_id') . ' = ' . $db->q((int) $stockId[$i]);

					$query = $db->getQuery(true)
						->update($db->qn($table))
						->set($fields)
						->where($conditions);

					$db->setQuery($query)->execute();

					$affectedRow = $db->getAffectedRows();

					if ($affectedRow > 0)
					{
						break;
					}
				}
			}
		}

		return true;
	}

	/**
	 * Replace stockroom amount detail
	 *
	 * @param   int  $templateDesc  Template desciption
	 * @param   int  $sectionId     Section id
	 * @param   int  $section       Section
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function replaceStockroomAmountDetail($templateDesc = "", $sectionId = 0, $section = "product")
	{
		if (strpos($templateDesc, '{stockroom_detail}') !== false)
		{
			$productinstock = "";

			if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
			{
				$list = self::getStockroomAmountDetailList($sectionId, $section);

				for ($i = 0, $in = count($list); $i < $in; $i++)
				{
					$productinstock .= "<div><span>" . $list[$i]->stockroom_name . "</span>:<span>" . $list[$i]->quantity . "</span></div>";
				}
			}

			$templateDesc = str_replace('{stockroom_detail}', $productinstock, $templateDesc);
		}

		return $templateDesc;
	}

	/**
	 * Get stock amount image
	 *
	 * @param   int  $sectionId    Section id
	 * @param   int  $section      Section
	 * @param   int  $stockAmount  Stockroom amount
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getStockAmountImage($sectionId = 0, $section = "product", $stockAmount = 0)
	{
		$list = array();

		if (Redshop::getConfig()->get('USE_STOCKROOM') == 1)
		{
			if ($stockAmount == 0)
			{
				$stockAmount = self::getStockAmountwithReserve($sectionId, $section);
			}

			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('*')
				->from($db->qn('#__redshop_stockroom_amount_image', 'sm'))
				->leftJoin($db->qn('#__redshop_product_stockroom_xref', 'sx') . ' ON ' . $db->qn('sx.stockroom_id') . ' = ' . $db->qn('sm.stockroom_id'))
				->leftJoin($db->qn('#__redshop_stockroom', 's') . ' ON ' . $db->qn('sx.stockroom_id') . ' = ' . $db->qn('s.stockroom_id'))
				->where($db->qn('sx.quantity') . ' > 0')
				->where($db->qn('sx.product_id') . ' = ' . $db->q('sectionId'));

			$query1 = $query->where($db->qn('stock_option') . ' = 2')
				->where($db->qn('stock_quantity') . ' = ' . $db->q((int) $stockAmount));

			$list = $db->setQuery($query1)->loadObjectList();

			if (count($list) <= 0)
			{
				$query1 = $query->where($db->qn('stock_option') . ' = 1')
					->where($db->qn('stock_quantity') . ' < ' . $db->q((int) $stockAmount))
					->order($db->qn('stock_quantity') . ' DESC')
					->order($db->qn('s.max_del_time') . ' ASC');

				$list = $db->setQuery($query1)->loadObjectList();

				if (count($list) <= 0)
				{
					$query1 = $query->where($db->qn('stock_option') . ' = 3')
						->where($db->qn('stock_quantity') . ' > ' . $db->q((int) $stockAmount))
						->order($db->qn('stock_quantity') . ' ASC')
						->order($db->qn('s.max_del_time') . ' ASC');

					$list = $db->setQuery($query1)->loadObjectList();
				}
			}
		}

		return $list;
	}

	/**
	 * Get reserved Stock
	 *
	 * @param   int  $sectionId  Section id
	 * @param   int  $section    Section
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getReservedStock($sectionId, $section = "product")
	{
		if (Redshop::getConfig()->get('IS_PRODUCT_RESERVE') && Redshop::getConfig()->get('USE_STOCKROOM'))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('SUM(qty)')
				->from($db->qn('#__redshop_cart'))
				->where($db->qn('product_id') . ' = ' . $db->q((int) $sectionId))
				->where($db->qn('section') . ' = ' . $db->q($section));

			return (int) $db->setQuery($query)->loadResult();
		}

		return 0;
	}

	/**
	 * Get current User reserved stock
	 *
	 * @param   int  $sectionId  Section id
	 * @param   int  $section    Section
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getCurrentUserReservedStock($sectionId, $section = "product")
	{
		if (Redshop::getConfig()->get('IS_PRODUCT_RESERVE') && Redshop::getConfig()->get('USE_STOCKROOM'))
		{
			$db = JFactory::getDbo();
			$sessionId = session_id();

			$query = $db->getQuery(true)
				->select('SUM(qty)')
				->from($db->qn('#__redshop_cart'))
				->where($db->qn('product_id') . ' = ' . $db->q((int) $sectionId))
				->where($db->qn('session_id') . $db->q($sessionId))
				->where($db->qn('section') . ' = ' . $db->q($section));

			return (int) $db->setQuery($query)->loadResult();
		}

		return 0;
	}

	/**
	 * Delete expired cart product
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function deleteExpiredCartProduct()
	{
		if (Redshop::getConfig()->get('IS_PRODUCT_RESERVE') && Redshop::getConfig()->get('USE_STOCKROOM'))
		{
			$db = JFactory::getDBO();
			$time = time() - (Redshop::getConfig()->get('CART_TIMEOUT') * 60);

			$conditions = array(
				$db->qn('time') . ' < ' . $db->q($time)
			);

			$query->delete($db->qn('#__redshop_cart'))
				->where($conditions);

			$db->setQuery($query)->execute();
		}

		return true;
	}

	/**
	 * Delete cart after empty
	 *
	 * @param   int  $sectionId  Section id
	 * @param   int  $section    Section
	 * @param   int  $quantity   Stockroom quantity
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function deleteCartAfterEmpty($sectionId = 0, $section = "product", $quantity = 0)
	{
		if (Redshop::getConfig()->get('IS_PRODUCT_RESERVE') && Redshop::getConfig()->get('USE_STOCKROOM'))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->where($db->qn('session_id') . ' = ' . $db->q(session_id()));

			if ($sectionId != 0)
			{
				$query->where($db->qn('product_id') . ' = ' . (int) $sectionId)
					->where($db->qn('section') . ' = ' . $db->q($section));
			}

			if ($quantity)
			{
				$query->select($db->qn('qty'))
					->from($db->qn('#__redshop_cart'));

				$qty = (int) $db->setQuery($query)->loadResult();
				$query->clear('select')
					->clear('from');

				if ($qty - (int) $quantity > 0)
				{
					$query->update($db->qn('#__redshop_cart'))
						->set($db->qn('qty') . ' = ' . $db->q(($qty - (int) $quantity)));
				}
				else
				{
					$query->delete($db->qn('#__redshop_cart'));
				}
			}
			else
			{
				$query->delete($db->qn('#__redshop_cart'));
			}

			$db->setQuery($query)->execute();
		}

		return true;
	}

	/**
	 * Add reserved stock
	 *
	 * @param   int  $sectionId  Section id
	 * @param   int  $quantity   Stockroom quantity
	 * @param   int  $section    Section
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function addReservedStock($sectionId, $quantity = 0, $section = "product")
	{
		if (Redshop::getConfig()->get('IS_PRODUCT_RESERVE') && Redshop::getConfig()->get('USE_STOCKROOM'))
		{
			$db = JFactory::getDBO();
			$sessionId = session_id();
			$time = time();

			$query = $db->getQuery(true)
				->select($db->qn('qty'))
				->from($db->qn('#__redshop_cart'))
				->where($db->qn('session_id') . ' = ' . $db->q($sessionId))
				->where($db->qn('product_id') . ' = ' . (int) $sectionId)
				->where($db->qn('section') . ' = ' . $db->q($section));

			$qty = $db->setQuery($query)->loadResult();

			if ($qty !== null)
			{
				$query = $db->getQuery(true)
					->update($db->qn('#__redshop_cart'))
					->set($db->qn('qty') . ' = ' . $db->q((int) $quantity))
					->set($db->qn('time') . ' = ' . $db->q($time))
					->where($db->qn('session_id') . ' = ' . $db->q($sessionId))
					->where($db->qn('product_id') . ' = ' . (int) $sectionId)
					->where($db->qn('section') . ' = ' . $db->q($section));
			}
			else
			{
				$query = $db->getQuery(true)
					->insert($db->qn('#__redshop_cart'))
					->columns($db->qn('session_id'), $db->qn('product_id'), $db->qn('qty'), $db->qn('time'), $db->qn('section'))
					->values($db->q($sessionId) . ',' . $db->q((int) $sectionId) . ',' . $db->q((int) $quantity) . ',' . $db->q($time) . ',' . $db->q($section));
			}

			$db->setQuery($query)->execute();
		}

		return true;
	}

	/**
	 * Get stockroom
	 *
	 * @param   int  $stockroomId  Stockroom id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getStockroom($stockroomId)
	{
		// Sanitize ids
		$stockroomId = explode(',', $stockroomId);
		JArrayHelper::toInteger($stockroomId);

		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__redshop_stockroom'))
			->where($db->qn('stockroom_id') . ' IN (' . implode(',', $stockroomId) . ')')
			->where($db->qn('published') . ' = 1');

		return $db->setQuery($query)->loadObjectlist();
	}

	/**
	 * Get min delivery time
	 * 
	 * @param   int  $stockroomId  Stockroom id
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getStockroom_maxdelivery($stockroomId)
	{
		// Sanitize ids
		$stockroomId = explode(',', $stockroomId);
		JArrayHelper::toInteger($stockroomId);

		$db = JFactory::getDBO();
		$query = $db->getQuery(true)
			->select($db->qn('max_del_time'))
			->select($db->qn('delivery_time'))
			->from($db->qn('#__redshop_stockroom'))
			->where($db->qn('stockroom_id') . ' IN (' . implode(',', $stockroomId) . ')')
			->where($db->qn('published') . ' = 1')
			->order($db->qn('max_del_time') . 'DESC');

		return $db->setQuery($query)->loadObjectlist();
	}

	/**
	 * Get date diff
	 *
	 * @param   int  $endDate    End date
	 * @param   int  $beginDate  Begin date
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getdatediff($endDate, $beginDate)
	{
		$epoch_1 = mktime(0, 0, 0, date("m", $endDate), date("d", $endDate), date("Y", $endDate));
		$epoch_2 = mktime(0, 0, 0, date("m", $beginDate), date("d", $beginDate), date("Y", $beginDate));
		$dateDiff = $epoch_1 - $epoch_2;
		$fullDays = floor($dateDiff / (60 * 60 * 24));

		return $fullDays;
	}

	/**
	 * Get final stock of product
	 *
	 * @param   int  $productId  Product id
	 * @param   int  $totalAtt   Total attribute
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getFinalStockofProduct($productId, $totalAtt)
	{
		$productHelper = productHelper::getInstance();

		$isStockExists = self::isStockExists($productId);

		if ($totalAtt > 0 && !$isStockExists)
		{
			$property = $productHelper->getAttibuteProperty(0, 0, $productId);

			for ($att_j = 0; $att_j < count($property); $att_j++)
			{
				$isSubpropertyStock = false;
				$subProperty = $productHelper->getAttibuteSubProperty(0, $property[$att_j]->property_id);

				for ($sub_j = 0; $sub_j < count($subProperty); $sub_j++)
				{
					$isSubpropertyStock = self::isStockExists($subProperty[$sub_j]->subattribute_color_id, 'subproperty');

					if ($isSubpropertyStock)
					{
						$isStockExists = $isSubpropertyStock;
						break;
					}
				}

				if ($isSubpropertyStock)
				{
					break;
				}
				else
				{
					$isPropertystock = self::isStockExists($property[$att_j]->property_id, "property");

					if ($isPropertystock)
					{
						$isStockExists = $isPropertystock;
						break;
					}
				}
			}
		}

		return $isStockExists;
	}

	/**
	 * Get final pre-order stock of product
	 *
	 * @param   int  $productId  Product id
	 * @param   int  $totalAtt   Total attribute
	 *
	 * @return mixed
	 *
	 * @since  2.0.0.3
	 */
	public static function getFinalPreorderStockofProduct($productId, $totalAtt)
	{
		$productHelper = productHelper::getInstance();

		$isStockExists = self::isPreorderStockExists($productId);

		if ($totalAtt > 0 && !$isStockExists)
		{
			$property = $productHelper->getAttibuteProperty(0, 0, $productId);

			for ($att_j = 0; $att_j < count($property); $att_j++)
			{
				$isSubpropertyStock = false;
				$subProperty = $productHelper->getAttibuteSubProperty(0, $property[$att_j]->property_id);

				for ($sub_j = 0; $sub_j < count($subProperty); $sub_j++)
				{
					$isSubpropertyStock = self::isPreorderStockExists($subProperty[$sub_j]->subattribute_color_id, 'subproperty');

					if ($isSubpropertyStock)
					{
						$isStockExists = $isSubpropertyStock;
						break;
					}
				}

				if ($isSubpropertyStock)
				{
					break;
				}
				else
				{
					$isPropertystock = self::isPreorderStockExists($property[$att_j]->property_id, "property");

					if ($isPropertystock)
					{
						$isStockExists = $isPropertystock;
						break;
					}
				}
			}
		}

		return $isStockExists;
	}
}
