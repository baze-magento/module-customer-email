<?php

namespace Baze\CustomerEmail\Console\Command;

use Magento\Framework\App\State;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\CustomerFactory;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CustomerEmailWelcomeCommand extends Command
{
	protected $appState;
	protected $customerFactory;
	protected $emailNotification;
	protected $storeManager;

	const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'customer/create_account/email_no_password_template';

	public function __construct(State $appState, CustomerFactory $customerFactory)
	{
		$this->appState = $appState;
		$this->customerFactory = $customerFactory;
		parent::__construct();
	}

	protected function configure()
	{
		$this->setName('customer:email:welcome')
			->setDescription('Send welcome email to all users in a specified website');
		$this->addArgument('website', InputArgument::REQUIRED, 'Send welcome emails to all users in this website');
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$website = $input->getArgument('website');
		$websiteId = getStoreManager()->getWebsites(false, true)[$website]->getId(); // $withDefault, $codeKey
		$output->writeln("<info>Sending welcome emails to all users in '${website}', ID ${websiteId}…</info>");

		$customers = $this->customerFactory->create()->getCollection();
		$customers->addFieldToFilter('website_id', $websiteId);
		foreach ($customers as $customer) {
			$storeId = $customer->getStoreId();
			$output->writeln("newAccount($customer, $templateType, $redirectUrl, $storeId)");
			// $this->getEmailNotification()->newAccount($customer, $templateType, $redirectUrl, $customer->getStoreId());
		}
	}

	private function getEmailNotification()
	{
		if (!($this->emailNotification instanceof EmailNotificationInterface)) {
			$this->emailNotification = ObjectManager::getInstance()->get(EmailNotificationInterface::class);
		}
		return $this->emailNotification;
	}

	private function getStoreManager()
	{
		if (!($this->storeManager instanceof StoreManagerInterface)) {
			$this->storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
		}
		return $this->storeManager;
	}
}
