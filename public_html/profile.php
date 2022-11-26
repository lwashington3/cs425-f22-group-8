<?php
require_once "api/ClassFiles/DataBase.php";
require_once "api/constants.php";

try{
	$db = new DataBase();
	$user = $db->getCurrentUserId();
} catch(PGException $PGException){
	http_response_code(500);
	header("Response: " . $PGException->getMessage());
	return;
}

if(!$db->isLoggedIn()){
	header("Location: " . HTTPS_HOST . "/");
	return;
}

$accounts = $user->getAccounts();
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>WCS Banking</title>
	<link href="/css/menu_style.css" type="text/css" rel="stylesheet"/>
	<link href="/css/account_tables.css" type="text/css" rel="stylesheet"/>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
</head>
<body>
<div id="content">
	<h2>My Accounts</h2>
	<table>
		<tr>
			<th>Account Name</th>
			<th>Balance</th>
			<th>Type</th>
			<th>Interest</th>
			<th>Monthly Fee</th>
			<th>Can Go Negative</th>
		</tr>
		<?php foreach($user->getAccounts() as $account) { ?>
			<tr>
				<td><?php echo $account->getName(); ?></td>
				<td><?php echo $account->getBalance(); ?></td>
				<td><?php echo $account->getType(); ?></td>
				<td><?php echo $account->getInterest(); ?>%</td>
				<td>$<?php echo $account->getMonthlyFee(); ?></td>
				<td><?php if($account->canGoNegative()) { echo "True"; } else{ echo "False";} ?></td>
			</tr>
		<?php }; ?>
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
