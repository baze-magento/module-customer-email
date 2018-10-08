<?php

namespace Baze\CustomerEmail\Console\Command;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManagerInterface;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CustomerEmailWelcomeCommand extends Command
{
	protected $appState;
	protected $customerFactory;
	protected $customerRepository;
	protected $mathRandom;
  protected $accountManagement;
	protected $emailNotification;
	protected $storeManager;

	const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'customer/create_account/email_no_password_template';

	public function __construct(State $appState, CustomerFactory $customerFactory, CustomerRepositoryInterface $customerRepository, Random $mathRandom, AccountManagementInterface $accountManagement)
	{
		$this->appState = $appState;
		$this->customerFactory = $customerFactory;
		$this->customerRepository = $customerRepository;
		$this->mathRandom = $mathRandom;
		$this->accountManagement = $accountManagement;
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
		try {
			$this->appState->setAreaCode('adminhtml');
		} catch (\Exception $e) {
			try {
				$this->appState->setAreaCode('adminhtml');
			} catch (\Exception $e) {
				// area code already set
			}
		}

		$websiteCode = $input->getArgument('website');
		$website = $this->getStoreManager()->getWebsites(false, true)[$websiteCode]; // $withDefault, $codeKey
		$websiteId = $website->getId();
		$websiteName = $website->getName();
		$storeIds = $website->getStoreIds();
		$output->writeln("<info>Sending welcome emails to all users in '${websiteName} (ID ${websiteId}:${websiteCode})'…</info>");

		$customerIntercepts = $this->customerFactory->create()->getCollection();
		$customerIntercepts->addFieldToFilter('website_id', $websiteId);
		$succeeded = 0;
		$failed = 0;
		foreach ($customerIntercepts as $customerIntercept) {
			$email = $customerIntercept->getEmail();
			$storeId = $customerIntercept->getStoreId();
			if (in_array($storeId, $storeIds, true)) {
				$customer = $this->customerRepository->get($email, $websiteId);
				$newLinkToken = $this->mathRandom->getUniqueHash();
				$accountManagement->changeResetPasswordLinkToken($customer, $newLinkToken);
				//$customer->changeResetPasswordLinkToken($newLinkToken);
				$output->writeln("$email in '${websiteCode}:${storeId}' (new token)");
				// $this->getEmailNotification()->newAccount($customer, $templateType, $redirectUrl, $storeId);
				$this->getEmailNotification()->newAccount($customer, 'registered_no_password', '', $storeId);
				$succeeded++;
			} else {
				$output->writeln("<warn>$email in store '$storeId' not of '$websiteCode'</warn>");
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
