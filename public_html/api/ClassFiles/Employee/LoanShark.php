<?php

class LoanShark extends Employee
{
	public function __construct(int $employee_id)
	{
		parent::__construct($employee_id, new LoanConfig());
	}

	/**
	 * @return Loan[]
	 */
	public function getRequestedLoans(){

	}

	public function approveLoan(Loan $loan){

	}
}