<?php

namespace Baze\CustomerEmail\Console\Command;

use Magento\Framework\App\State;
use Magento\Customer\Model\CustomerFactory;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CustomerEmailWelcomeCommand extends Command
{
	protected $appState;
	protected $customerFactory;

	public function __construct(State $appState, CustomerFactory $customerFactory)
	{
		$this->appState = $appState;
		$this->customerFactory = $customerFactory;
		parent::__construct();
	}

	protected function configure()
	{
		$this->setName('customer:email:welcome')
			->setDescription('Send welcome email to all users in the specified website');
		$this->addArgument('website', InputArgument::REQUIRED, 'Send welcome emails to all users in this website');
		parent::configure();
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$website = $input->getArgument('website');
		$output->writeln("<info>Sending welcome emails to all users in ${website}…</info>");
	}
}
