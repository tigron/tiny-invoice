<?php
/**
 * migration:create command for Skeleton Console
 *
 * @author Gerry Demaret <gerry@tigron.be>
 * @author Christophe Gosiau <christophe@tigron.be>
 * @author David Vandemaele <david@tigron.be>
 */

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Command\Command;
use \Skeleton\Transaction\Daemon;

class Invoice_Delete extends \Skeleton\Console\Command {

	/**
	 * Configure the Create command
	 *
	 * @access protected
	 */
	protected function configure() {
		$this->setName('tiny:invoice:delete');
		$this->setDescription('Delete an invoice');
		$this->addArgument('invoice_id', InputArgument::REQUIRED, 'invoice_id');
	}

	/**
	 * Execute the Command
	 *
	 * @access protected
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$invoice_id = $input->getArgument('invoice_id');
	
		try {
			$invoice = Invoice::get_by_number($invoice_id);
		} catch (\Exception $e) {
		    $output->writeln('Invoice ' . $invoice_id . ' not found');
			return Command::SUCCESS;		    
		}

	    $output->writeln('Invoice ' . $invoice_id);
	    $output->writeln('Created ' . $invoice->created);	    
	    $output->writeln('Customer ' . $invoice->customer->get_display_name());					    				

		$helper = $this->getHelper('question');
		$question = new ConfirmationQuestion('Are you sure you want to delete invoice ' . $invoice_id . '? ', false);
		if (!$helper->ask($input, $output, $question)) {
		    $output->writeln('No invoice deleted');
			return Command::SUCCESS;
		}
		$invoice->delete();

		return 0;
	}

}
