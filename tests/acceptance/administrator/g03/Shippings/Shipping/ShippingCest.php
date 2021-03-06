<?php
/**
 * @package     redSHOP
 * @subpackage  Cest
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use AcceptanceTester\ShippingSteps;
use AcceptanceTester\UserManagerJoomla3Steps;
use AcceptanceTester\ProductManagerJoomla3Steps;
use AcceptanceTester\CategoryManagerJoomla3Steps;
use Configuration\ConfigurationSteps;
use AcceptanceTester\CheckoutWithShippingRateSteps;
use AcceptanceTester\OrderManagerJoomla3Steps;

/**
 * Class ShippingCest
 *
 * @package  AcceptanceTester
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage
 *
 * @since    2.1.0
 */
class ShippingCest
{
	/**
	 * @var \Faker\Generator
	 * @since 2.1.3
	 */
	public $faker;

	/**
	 * @var string
	 * @since 2.1.3
	 */
	public $shippingMethod;

	/**
	 * @var array
	 * @since 2.1.3
	 */
	public $shipping;

	/**
	 * @var array
	 * @since 2.1.3
	 */
	public $shippingSaveClose;

	/**
	 * @var string
	 * @since 2.1.3
	 */
	public $shippingNameEdit;

	/**
	 * @var string
	 * @since 2.1.3
	 */
	public $shippingNameSaveClose;

	/**
	 * @var int
	 * @since 2.1.3
	 */
	public $shippingRateEdit;

	/**
	 * @var array
	 * @since 2.1.3
	 */
	public $customerInformation;

	/**
	 * @var string
	 * @since 2.1.3
	 */
	public $categoryName;

	/**
	 * @var array
	 * @since 2.1.3
	 */
	public $product;

	/**
	 * @var string
	 * @since 2.1.3
	 */
	public $group;

	/**
	 * @var mixed
	 * @since 2.1.3
	 */
	public $total;

	/**
	 * ShippingCest constructor.
	 * @since 2.1.3
	 */
	public function __construct()
	{
		$this->faker = Faker\Factory::create();

		// Shipping info
		$this->shippingMethod = 'redSHOP - Standard Shipping';
		$this->shipping       = array(
			'shippingName' => $this->faker->bothify('TestingShippingRate ?##?'),
			'shippingRate' => 10
		);

		$this->shippingSaveClose = array(
			'shippingName' => $this->faker->bothify('TestingShippingRate ?##?'),
			'shippingRate' => 10
		);

		$this->shippingNameEdit      = $this->shipping['shippingName'] . ' edit';
		$this->shippingNameSaveClose = "TestingSave" . rand(1, 100);
		$this->shippingRateEdit      = rand(100, 1000);

		$this->customerInformation = array(
			"userName"      => $this->faker->userName,
			"email"         => $this->faker->email,
			"firstName"     => $this->faker->firstName,
			"lastName"      => $this->faker->lastName,
			"address"       => $this->faker->address,
			"postalCode"    => "700000",
			"city"          => "HCM",
			"country"       => "Denmark",
			"state"         => "Karnataka",
			"phone"         => $this->faker->phoneNumber,
			"shopperGroup"  => 'Default Private',
			'group'         => 'Registered'
		);

		$this->categoryName = $this->faker->bothify("Category Demo ?##?");
		$this->product = array(
			"name"          => $this->faker->bothify("Product Demo ?##?"),
			"number"        => $this->faker->numberBetween(999,9999),
			"price"         => $this->faker->numberBetween(1,900)
		);

		$this->total = $this->product['price'] + $this->shipping['shippingRate'];
	}

	/**
	 * @param AcceptanceTester $I
	 * @throws Exception
	 * @since 2.1.3
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
	}

	/**
	 * @param   AcceptanceTester      $I        Tester
	 * @param   \Codeception\Scenario $scenario Scenario
	 *
	 * @return  void
	 * @throws  Exception
	 * @since 2.1.3
	 */
	public function createShippingRate(AcceptanceTester $I, \Codeception\Scenario $scenario)
	{
		$I = new AcceptanceTester\ShippingSteps($scenario);
		$I->wantTo('Check create new Shipping rate with save button');
		$I->createShippingRateStandard($this->shippingMethod, $this->shipping, 'save');

		$I->wantTo('Check create new Shipping rate with save & close button');
		$I->createShippingRateStandard($this->shippingMethod, $this->shippingSaveClose, 'saveclose');

		$I->wantTo('Edit a shipping Rate');
		$I->editShippingRateStandard($this->shipping['shippingName'], $this->shippingNameEdit, $this->shippingRateEdit, 'save');

		$I->wantTo('Edit a shipping Rate with Save Close');
		$I->editShippingRateStandard($this->shippingNameEdit, $this->shipping['shippingName'], $this->shipping['shippingRate'], 'saveclose');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param $scenario
	 * @throws Exception
	 * @since 2.1.3
	 */
	public function CheckoutWithShippingRate(AcceptanceTester $I, $scenario)
	{
		$I->wantToTest('Create User');
		$I = new UserManagerJoomla3Steps($scenario);
		$I->addUser($this->customerInformation['userName'], $this->customerInformation['userName'], $this->customerInformation['email'], $this->customerInformation['group'],
			$this->customerInformation['shopperGroup'], $this->customerInformation['firstName'], $this->customerInformation['lastName'], 'saveclose');
		$I->editAddShipping($this->customerInformation['firstName'], $this->customerInformation['lastName'], $this->customerInformation['address'],
			$this->customerInformation['city'], $this->customerInformation['phone'], $this->customerInformation['postalCode']);

		$I->wantToTest('Create Category');
		$I = new CategoryManagerJoomla3Steps($scenario);
		$I->addCategorySave($this->categoryName);

		$I->wantToTest("Create Product");
		$I = new ProductManagerJoomla3Steps($scenario);
		$I->createProductSaveClose($this->product['name'], $this->categoryName, $this->product['number'], $this->product['price']);

		$I->wantToTest('Checkout with '.$this->shipping['shippingName'].' just create');
		$I = new CheckoutWithShippingRateSteps($scenario);
		$I->checkoutWithShippingRate($this->customerInformation['userName'], $this->customerInformation['userName'], $this->categoryName,
			$this->product['name'], $this->total, $this->shipping);

		$I->wantToTest('Check Order on Backend');
		$I = new ConfigurationSteps($scenario);
		$I->checkPriceTotal($this->product['price'], $this->customerInformation["firstName"], $this->customerInformation["firstName"], $this->customerInformation["lastName"],
			$this->product['name'], $this->categoryName, $this->shippingMethod);
	}

	/**
	 * @param ProductManagerJoomla3Steps $I
	 * @param $scenario
	 * @throws Exception
	 * @since 2.1.3
	 */
	public function clearAll(ProductManagerJoomla3Steps $I, $scenario)
	{
		$I->wantToTest('Delete Product');
		$I->deleteProduct($this->product['name']);

		$I->wantToTest('Delete Category');
		$I = new CategoryManagerJoomla3Steps($scenario);
		$I->deleteCategory($this->categoryName);

		$I->wantToTest('Delete User');
		$I = new UserManagerJoomla3Steps($scenario);
		$I->deleteUser($this->customerInformation['firstName']);

		$I->wantToTest('Delete Shipping Rate');
		$I = new ShippingSteps($scenario);
		$I->deleteShippingRate($this->shippingMethod, $this->shipping['shippingName']);

		$I->wantToTest('Delete Order');
		$I = new OrderManagerJoomla3Steps($scenario);
		$I->deleteOrder($this->customerInformation['firstName']);
	}
}
