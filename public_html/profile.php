<?php
require_once "api/ClassFiles/DataBase.php";
require_once "api/constants.php";

try{
	$db = new DataBase();
	$user = $db->getCurrentUserId();
} catch(PGException $pgError){
	http_response_code(500);
	respond($pgError->getMessage());  # TODO: Add a note telling users how to access the transactions.
	return;
}

if(!$db->isLoggedIn()){
	header("Location: " . HTTPS_HOST . "/");
	return;
}

$loans = $user->getLoans();
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Banking</title>
	<link href="/css/menu_style.css" type="text/css" rel="stylesheet"/>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
	<link href="/css/sidebar.css" type="text/css" rel="stylesheet"/>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<script type="text/javascript" src="/scripts/transactions.js"></script>
	<script type="text/javascript">
		function openSidebar () {
			let side = document.getElementById("page-side");
			side.classList.add("show");
		}

		function closeSidebar () {
			let side = document.getElementById("page-side");
			side.classList.remove("show");
		}
	</script>
</head>
<body class="sidebar" onload="getAccounts()">
<nav id="page-side">
	<div id="leftcontent">
		<div id="account-number" class="side-account-info">
			<div class="label">Account Number:</div><div id="number">The account's number</div>
		</div>
		<div id="account-name" class="side-account-info">
			<div class="label">Name: </div><div id="name">The account's name</div> <!-- Should be clickable to change -->
		</div>
		<div id="account-balance" class="side-account-info">
			<div class="label">Balance: </div><div id="balance">The account's balance</div> <!--The rest of these should just be displayed.-->
		</div>
		<div id="account-interest" class="side-account-info">
			<div class="label">Interest: </div><div id="interest">The account's interest</div>
		</div>
		<div id="account-monthly-fees" class="side-account-info">
			<div class="label">Monthly Fees: </div><div id="monthly_fees">The account's monthly fees</div>
		</div>
		<div id="account-overdrawn" class="side-account-info">
			<div class="label">Can be Overdrawn: </div><div id="overdrawn">If the account can be overdrawn.</div>
		</div>
	</div>
	<div id="rightcontent">
		<input type="text" id="transaction" name="transaction" pattern="Withdrawal|Deposit|Transfer" list="transactions" placeholder="Transaction Type" onchange="checkTransactionType()"><br>
		<datalist id="transactions">
			<option>Withdrawal</option>
			<option>Deposit</option>
			<option>Transfer</option>
		</datalist>
		$<input name="amount" id="amount" step="0.01" min="0" type="currency" max="1000" placeholder="Amount" required><br>
		<input name="transfer_to_account_number" id="transfer_to_account_number" placeholder="Recipient Account Number" hidden><br id="transfer_break" hidden>
		<input name="description" id="description" type="text" placeholder="Transaction Description"><br>
		<input name="do_transaction" id="do_transaction" type="submit" value="Do the Transaction" onclick="transact()">
	</div>
	<hr>
	<div id="scheduling">
		<button id="pending_transactions" onclick="getPendingTransactions()">See Pending Transactions</button>
		<label for="statement_month">Input Month</label><input type="month" id="statement_month" name="statement_month" placeholder="mm-yyyy" value="" min="2022-11-01" max="<?php echo date("Y-m-d")?>">
		<button id="see_statement" onclick="getMonthlyStatement()">See Monthly Statement</button>
		<table id="schedule" class="profile_info">
			<tr>
				<th>Time</th>
				<th>Amount</th>
				<th>Account Balance</th>
				<th>Description</th>
			</tr>
		</table>
	</div>
</nav>
<div id="page-main">
	<h2>My Accounts</h2>
	<table id="accounts" class="profile_info">
		<tr>
			<th>Account Name</th>
			<th>Balance</th>
			<th>Type</th>
			<th>Interest</th>
			<th>Monthly Fee</th>
			<th>Can Be Overdrawn</th>
		</tr>
	</table>
	<h2>My Loans</h2>
	<table id="loans" class="profile_info">
		<tr>
			<th>Loan Name</th>
			<th>Initial Amount</th>
			<th>Remaining Amount</th>
			<th>APR</th>
		</tr>
		<?php if(is_array($loans)){ foreach($loans as $loan) { ?>
			<tr>
				<td><?php echo $loan->getName(); ?></td>
				<td>$<?php echo sprintf("%.2f", $loan->getInitialAmount()); ?></td>
				<td>$<?php echo sprintf("%.2f", $loan->getAmountRemaining()); ?></td>
				<td><?php echo $loan->getAPR(); ?>%</td>
			</tr>
		<?php }}; ?>
	</table>
	<nav class="floating-menu">
		<?php if(!$db->isLoggedIn()): ?>
			<h3>We sold you?</h3>
			<a href="/login">Log In</a>
			<a href="/signup">Sign Up</a>
		<?php else: ?>
			<h3>Hello <?php try {
					echo $user->getFirstName();
				} catch (PGException $e) {
					echo "Internal Server Error";
				} ?></h3>
			<a href="/profile">Check My Profile</a>
			<a href="/api/logout">Logout</a>
		<?php endif; ?>

	</nav>
</div>
</body>
</html>
