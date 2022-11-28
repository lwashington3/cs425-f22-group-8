<?php

require_once "User.php";
require_once "CS425Class.php";
require_once (dirname(__DIR__) . "/ConfigFiles/ProfileConfig.php");
require_once (dirname(__DIR__) . "/tools.php");

class Loan extends CS425Class
{
	private int $loan_number;  // # TODO: Make this const

	public function __construct($loan_number){
		parent::__construct(new LoanConfig());
		$this->$loan_number = $loan_number;
	}

	public function getLoanNumber(){ return $this->loan_number; }

	public function getName(){
		return $this->getBasicResult(sprintf("SELECT loan_name FROM LoanApprovals WHERE loan_number = %d", $this->loan_number));
	}

	public function getInitialAmount(){
		return $this->getBasicResult(sprintf("SELECT initial_amount FROM LoanApprovals WHERE loan_number = %d", $this->loan_number));
	}

	public function getApprovalDate(){
		return $this->getBasicResult(sprintf("SELECT approval_date FROM LoanApprovals WHERE loan_number = %d", $this->loan_number));
	}

	public function getAmountRemaining(){
		return $this->getBasicResult(sprintf("SELECT amount_remaining FROM LoanApprovals WHERE loan_number = %d", $this->loan_number));
	}

	public function getPaybackPeriod(){
		return $this->getBasicResult(sprintf("SELECT payback_period FROM LoanApprovals WHERE loan_number = %d", $this->loan_number));
	}

	public function getCompoundingPerYear(){
		return $this->getBasicResult(sprintf("SELECT compounding_per_year FROM LoanApprovals WHERE loan_number = %d", $this->loan_number));
	}

	public function getAPR(){
		return $this->getBasicResult(sprintf("SELECT apr FROM LoanApprovals WHERE loan_number = %d", $this->loan_number));
	}
}