<?php
/**
 * @package     RedSHOP.Backend
 * @subpackage  Table
 *
 * @copyright   Copyright (C) 2008 - 2019 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

class Tabletransaction_coupon_detail extends JTable
{
    public $transaction_coupon_id = null;

    public $coupon_id = null;

    public $coupon_code = null;

    public $coupon_value = null;

    public $userid = null;

    public $trancation_date = null;

    public $published = null;

    public function __construct(&$db)
    {
        $this->_table_prefix = '#__redshop_';

        parent::__construct($this->_table_prefix . 'coupons_transaction', 'transaction_coupon_id', $db);
    }

    public function bind($array, $ignore = '')
    {
        if (array_key_exists('params', $array) && is_array($array['params'])) {
            $registry = new JRegistry;
            $registry->loadArray($array['params']);
            $array['params'] = $registry->toString();
        }

        return parent::bind($array, $ignore);
    }
}
