<?php
/**
 * @package     redSHOP
 * @subpackage  Cest
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Cest\AbstractCest;
use AcceptanceTester\ProductManagerJoomla3Steps as ProductManagerSteps;

/**
 * Category cest
 *
 * @package  AcceptanceTester
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage
 *
 * @since    1.4
 */
class CategoryCest extends AbstractCest
{
	use  Cest\Traits\CheckIn, Cest\Traits\Publish, Cest\Traits\Delete;

	/**
	 * Name field, which is use for search
	 *
	 * @var string
	 * @since 1.4.0
	 */
	public $nameField = 'name';


	/**
	 * Method for set new data.
	 *
	 * @return  array
	 * @since 1.4.0
	 */
	protected function prepareNewData()
	{
		return array(
			'name'        => $this->faker->bothify('Category Name ?##?'),
			'type'        => 'Total',
			'value'       => '100',
			'effect'      => 'Global',
			'amount_left' => '10'
		);
	}

	/**
	 * Abstract method for run after complete create item.
	 *
	 * @param   \AcceptanceTester      $tester    Tester
	 * @param   \Codeception\Scenario  $scenario  Scenario
	 *
	 * @return  void
	 *
	 * @depends testItemCreate
	 * @since 1.4.0
	 */
	public function deleteDataSave(\AcceptanceTester $tester, \Codeception\Scenario $scenario)
	{
		$tester->wantTo('Run after create item with save button ');
		$stepClass = $this->stepClass;

		/** @var CategorySteps $tester */
		$tester = new $stepClass($scenario);
		$tester->deleteItem('New' . $this->dataNew['name']);

	}

	/**
	 * Abstract method for run after complete create item.
	 *
	 * @param   \AcceptanceTester      $tester    Tester
	 * @param   \Codeception\Scenario  $scenario  Scenario
	 *
	 * @return  void
	 *
	 * @depends testItemCreateSaveClose
	 * @since 1.4.0
	 */
	public function deleteDataSaveClose(\AcceptanceTester $tester, \Codeception\Scenario $scenario)
	{
		$tester->wantTo('Run after create item with save button ');
		$stepClass = $this->stepClass;

		/** @var CategorySteps $tester */
		$tester = new $stepClass($scenario);
		$tester->deleteItem('New' . $this->dataNew['name']);

	}

	/**
	 * Abstract method for run after complete create item.
	 *
	 * @param   \AcceptanceTester      $tester    Tester
	 * @param   \Codeception\Scenario  $scenario  Scenario
	 *
	 * @return  void
	 * @throws \Exception
	 * @depends testItemCreateSaveNew
	 * @since 1.4.0
	 */
	public function afterTestItemCreate(\AcceptanceTester $tester, \Codeception\Scenario $scenario)
	{
		$tester->wantTo('Run after create category test suite');
		$stepClass = $this->stepClass;

		/** @var CategorySteps $tester */
		$tester            = new $stepClass($scenario);
		$nameCategoryChild = $this->faker->bothify('CategiryChild ?##? ');
		$productName       = $this->faker->bothify('ProductCategory ?##?');
		$productNameSecond = $this->faker->bothify('Product ?##?');
		$productNumber     = $this->faker->numberBetween(100, 10000);
		$price             = $this->faker->numberBetween(1, 100);

		$tester->addCategoryChild('New' . $this->dataNew['name'], $nameCategoryChild, 3);

		$tester = new ProductManagerSteps($scenario);
		$tester->createProductSaveClose($productName, 'New' . $this->dataNew['name'], $productNumber, $price);
		$tester->createProductSaveClose($productNameSecond, $nameCategoryChild, $productNameSecond, $price);

		$stepClass = $this->stepClass;

		/** @var CategorySteps $tester */
		$tester = new $stepClass($scenario);
		$tester->addCategoryAccessories($this->dataNew['name'], 4, $productNameSecond);

		$tester = new ProductManagerSteps($scenario);
		$tester->deleteProduct($productName);
		$tester->deleteProduct($productNameSecond);

		$stepClass = $this->stepClass;

		/** @var CategorySteps $tester */
		$tester = new $stepClass($scenario);
		$tester->deleteItem($nameCategoryChild);
	}

	/**
	 * @return array
	 * @since 1.4.0
	 */
	protected function prepareEditData()
	{
		return array(
			'name'        => 'New' . $this->dataNew['name'],
			'type'        => 'Total',
			'value'       => '100',
			'effect'      => 'Global',
			'amount_left' => '10'
		);
	}
}
