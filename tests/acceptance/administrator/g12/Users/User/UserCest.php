<?php
/**
 * @package     redSHOP
 * @subpackage  Cest
 * @copyright   Copyright (C) 2008 - 2020 redCOMPONENT.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use AcceptanceTester\UserManagerJoomla3Steps;

/**
 * Class UserCest
 *
 * @package  AcceptanceTester
 *
 * @link     http://codeception.com/docs/07-AdvancedUsage
 *
 * @since    1.4
 */
class UserCest
{
	/**
	 * @var \Faker\Generator
	 * @since 1.4.0
	 */
	protected $faker;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $userName;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $password;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $email;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $emailMissingUser;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $emailSave;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $shopperGroup;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $group;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $firstName;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $updateFirstName;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $lastName;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $firstNameSave;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $lastNameSave;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $emailWrong;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $userNameEdit;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $emailMatching;

	/**
	 * @var string
	 * @since 1.4.0
	 */
	protected $userMissing;

	/**
	 * UserCest constructor.
	 * @since 1.4.0
	 */
	public function __construct()
	{
		$this->faker            = Faker\Factory::create();
		$this->userName         = $this->faker->bothify('ManageUserAdministratorCest ?##?');
		$this->password         = $this->faker->bothify('Password ?##?');
		$this->email            = $this->faker->email;
		$this->emailMissingUser = $this->faker->email;
		$this->emailSave        = $this->faker->email;
		$this->shopperGroup     = 'Default Private';
		$this->group            = 'Public';
		$this->firstName        = $this->faker->bothify('ManageUserAdministratorCest FN ?##?');
		$this->updateFirstName  = 'Updating ' . $this->firstName;
		$this->lastName         = 'Last';
		$this->firstNameSave    = "FirstName";
		$this->lastNameSave     = "LastName";
		$this->emailWrong       = "email";
		$this->userNameEdit     = "UserNameSave" . $this->faker->randomNumber();
		$this->emailMatching    = $this->faker->email;
		$this->userMissing      = $this->faker->bothify('ManageUserMissingAdministratorCest ?##?');
	}

	/**
	 * @param AcceptanceTester $I
	 * @throws \Exception
	 * @since 1.4.0
	 */
	public function _before(AcceptanceTester $I)
	{
		$I->doAdministratorLogin();
	}

	/**
	 * Function add user with save and save &c lose button
	 *
	 * @param AcceptanceTester $I
	 * @param $scenario
     * @throws \Exception
	 * @since 1.4.0
	 */
	public function addUser(AcceptanceTester $I, $scenario)
	{
		$I->wantTo('Test User creation with save button in Administrator');
		$I = new UserManagerJoomla3Steps($scenario);
		$I->addUser($this->userName, $this->password, $this->email, $this->group, $this->shopperGroup, $this->firstName, $this->lastName, 'save');
		$I->addUser($this->userNameEdit, $this->password, $this->emailSave, $this->group, $this->shopperGroup, $this->firstNameSave, $this->lastNameSave, 'saveclose');
	}

	/**
	 *
	 * Function create user with missing field
	 *
	 * @param AcceptanceTester $I
	 * @param $scenario
	 * @since 1.4.0
     * @throws \Exception
	 */
	public function addUserMissing(AcceptanceTester $I, $scenario)
	{
		$I->wantTo('Test User creation with save button in Administrator');
		$I = new UserManagerJoomla3Steps($scenario);
		$I->addUserMissing($this->userName, $this->password, $this->email, $this->group, $this->shopperGroup, $this->firstNameSave, $this->lastNameSave, 'email');
		$I->addUserMissing($this->userMissing, $this->password, $this->emailWrong, $this->group, $this->shopperGroup, $this->firstName, $this->lastName, 'wrongemail');
		$I->addUserMissing($this->userNameEdit, $this->password, $this->emailMissingUser, $this->group, $this->shopperGroup, $this->firstName, $this->lastName, 'userName');
		$I->addUserMissing($this->userNameEdit, $this->password, $this->emailSave, $this->group, $this->shopperGroup, $this->firstNameSave, $this->lastNameSave, 'readyUser');
		$I->addUserMissing($this->userNameEdit . "editMail1", $this->password, $this->emailSave, $this->group, $this->shopperGroup, $this->firstNameSave, $this->lastNameSave, 'readyEmail');
		$I->addUserMissing($this->userNameEdit . "editMail1Test", $this->password, $this->emailMatching, $this->group, $this->shopperGroup, $this->firstNameSave, $this->lastNameSave, 'passwordNotMatching');
		$I->addUserMissing($this->userNameEdit . "editMail1", $this->password, $this->emailMatching, $this->group, 'Top', $this->firstNameSave, $this->lastNameSave, 'missingShopper');
		$I->addUserMissing($this->userNameEdit . "editMail1", $this->password, $this->emailSave, $this->group, $this->shopperGroup, $this->firstNameSave, $this->lastNameSave, 'firstName');
		$I->addUserMissing($this->userNameEdit . "editMail1", $this->password, $this->emailSave, $this->group, $this->shopperGroup, $this->firstNameSave, $this->lastNameSave, 'lastName');
	}

	/**
	 * @param AcceptanceTester $I
	 * @param $scenario
	 * @since 1.4.0
     * @throws \Exception
	 */
	public function addUserMissingJoomla(AcceptanceTester $I, $scenario)
	{
		$I->wantTo('Test User creation with save button in Administrator');
		$I = new UserManagerJoomla3Steps($scenario);
		$I->addUserMissing($this->userNameEdit . "editMail1", $this->password, $this->emailMatching, $this->group, $this->shopperGroup, $this->firstNameSave, $this->lastNameSave, 'missingJoomlaGroup');
	}

	/**
	 * Function to Test User Update in the Administrator
	 *
	 * @depends addUser
	 * @since 1.4.0
	 */
	public function updateUser(AcceptanceTester $I, $scenario)
	{
		$I->wantTo('Test if User gets updated in Administrator');
		$I = new AcceptanceTester\UserManagerJoomla3Steps($scenario);
		$I->editUser($this->firstName, $this->updateFirstName);
	}

	/**
	 * Function to Test User Update in the Administrator
	 *
	 * @depends addUser
     * @throws \Exception
	 * @since 1.4.0
	 */
	public function updateReadyUserName(AcceptanceTester $I, $scenario)
	{
		$I->wantTo('Test if User gets updated in Administrator');
		$I = new UserManagerJoomla3Steps($scenario);
		$I->editUserReady($this->updateFirstName, $this->userNameEdit);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param $scenario
	 * @since 1.4.0
     * @throws \Exception
	 */
	public function checkCloseButton(AcceptanceTester $I, $scenario)
	{
		$I->wantTo('Test if User gets updated in Administrator');
		$I = new UserManagerJoomla3Steps($scenario);
		$I->checkCloseButton($this->updateFirstName);
		$I->searchUser($this->updateFirstName);
	}

	/**
	 * Function to Test User Deletion
	 *
	 * @depends updateUser
	 * @since 1.4.0
	 * @throws \Exception
	 */
	public function deleteUser(AcceptanceTester $I, $scenario)
	{
		$I->wantTo('Deletion of User in Administrator');
		$I = new UserManagerJoomla3Steps($scenario);
		$I->deleteUser($this->updateFirstName, false);
	}

	/**
	 * @param AcceptanceTester $I
	 * @param $scenario
	 * @throws \Exception
	 * @since 1.4.0
	 */
	public function checkButtons(AcceptanceTester $I, $scenario)
	{
		$I->wantTo('Test to validate different buttons on Gift Card Views');
		$I = new UserManagerJoomla3Steps($scenario);
		$I->checkButtons('edit');
		$I->checkButtons('cancel');
		$I->checkButtons('delete');
	}
}
