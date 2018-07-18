<?php
/**
 * Checkout with discount total
 */

use AcceptanceTester\CategoryManagerJoomla3Steps;
use AcceptanceTester\DiscountSteps;
use AcceptanceTester\ProductCheckoutManagerJoomla3Steps;
use AcceptanceTester\ProductManagerJoomla3Steps;

/**
 * Class CheckoutDiscountTotalCest
 *
 * @since 1.6.0
 */
class CheckoutDiscountTotalCest
{
	/**
	 * @var \Faker\Generator
	 */
	public $faker;

	public function __construct()
	{
		$this->faker               = Faker\Factory::create();
		$this->ProductName         = 'ProductName' . rand(100, 999);
		$this->CategoryName        = "CategoryName" . rand(1, 100);
		$this->minimumPerProduct   = 1;
		$this->minimumQuantity     = 1;
		$this->maximumQuantity     = $this->faker->numberBetween(100, 1000);
		$this->discountStart       = "2016-12-12";
		$this->discountEnd         = "2017-05-23";
		$this->randomProductNumber = $this->faker->numberBetween(999, 9999);
		$this->randomProductPrice  = 100;

		$this->subtotal = "DKK 100,00";
		$this->Discount = "DKK 50,00";
		$this->Total    = "DKK 50,00";

		$this->discountName      = 'Discount' . rand(1, 100);
		$this->amount            = 150;
		$this->discountAmount    = 50;
		$this->startDate         = '2017-06-13';
		$this->endDate           = '2017-08-13';
		$this->shopperGroup      = 'Default Private';
		$this->discountType      = 'Total';
		$this->discountCondition = 'Lower';
	}

	/**
	 * Clean up data.
	 *
	 * @param $scenario
	 *
	 * @return  void
	 */
	public function deleteData($scenario)
	{
		$I = new RedshopSteps($scenario);
		$I->clearAllData();
	}

	/**
	 * Run before test.
	 *
	 * @param AcceptanceTester $I
	 *
	 * @return void
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
	}

	/**
	 * Step1 : delete all database
	 * Step1 : create category
	 * Step2 : create product have price is 100
	 * Step3 : Create Mass  and create discount lower 150 and have discount is 50
	 * Step4 : Goes on frontend and checkout with this product
	 * Step5 : Delete data
	 *
	 * @param AcceptanceTester $I
	 * @param                  $scenario
	 *
	 * @depends deleteData
	 */
	public function checkoutWithDiscountTotal(AcceptanceTester $I, $scenario)
	{
		$I->wantTo('Create Category in Administrator');
		$I = new CategoryManagerJoomla3Steps($scenario);
		$I->addCategorySave($this->CategoryName);

		$I = new ProductManagerJoomla3Steps($scenario);
		$I->wantTo('I Want to add product inside the category');
		$I->createProductSave(
			$this->ProductName,
			$this->CategoryName,
			$this->randomProductNumber,
			$this->randomProductPrice,
			$this->minimumPerProduct,
			$this->minimumQuantity,
			$this->maximumQuantity,
			$this->discountStart,
			$this->discountEnd
		);

		$I->wantTo('Test Discount creation with save and close button in Administrator');
		$I = new DiscountSteps($scenario);
		$I->wantTo('Create a Discount');
		$I->addDiscount(
			$this->discountName, $this->amount, $this->discountAmount, $this->shopperGroup, $this->discountType, $this->discountCondition
		);

		$I->wantTo('Checkout with discount at total');
		$I = new ProductCheckoutManagerJoomla3Steps($scenario);
		$I->checkoutWithDiscount($this->ProductName, $this->CategoryName, $this->subtotal, $this->Discount, $this->Total);

	}

	/**
	 * @param AcceptanceTester $I
	 * @param                  $scenario
	 *
	 * Clear database
	 *
	 * @depends checkoutWithDiscountTotal
	 */
	public function clearUp(AcceptanceTester $I, $scenario)
	{
		$I->wantTo('Delete all data');
		$I= new RedshopSteps($scenario);
		$I->clearAllData();
	}
}