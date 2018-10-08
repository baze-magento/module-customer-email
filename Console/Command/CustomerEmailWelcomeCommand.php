<?php

namespace Baze\CustomerEmail\Console\Command;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

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
		$websiteCode = $input->getArgument('website');
		$website = $this->getStoreManager()->getWebsites(false, true)[$websiteCode]; // $withDefault, $codeKey
		$websiteId = $website->getId();
		$websiteName = $website->getName();
		$storeIds = $website->getStoreIds();
		$output->writeln("<info>Sending welcome emails to all users in '${websiteName} (ID ${websiteId}:${websiteCode})'…</info>");

		$customers = $this->customerFactory->create()->getCollection();
		$customers->addFieldToFilter('website_id', $websiteId);
		$succeeded = 0
		$failed = 0
		foreach ($customers as $customer) {
			$email = $customer->getEmail();
			$storeId = $customer->getStoreId();
			if (in_array($storeId, $storeIds, true)) {
				$output->writeln("$email in '${websiteCode}:${storeId}'");
				// $this->getEmailNotification()->newAccount($customer, $templateType, $redirectUrl, $customer->getStoreId());
				$this->getEmailNotification()->newAccount($customer, 'registered_no_password', '', $customer->getStoreId());
				$succeeded++;
			} else {
				$output->writeln("<warn>$email in store '$storeId' not of '$website'</warn>");
				$failed++;
			}
		}

		$output->writeln("<info>Sent $succeeded welcome emails.</info>");
		$output->writeln("<info>Skipped $failed welcome emails.</info>");
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
