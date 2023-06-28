<?php
require_once "api/constants.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WCS Login</title>
	<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>"/>
	<link href="/css/wcss.php" type="text/css" rel="stylesheet"/>
	<link href="/css/forms.css" type="text/css" rel="stylesheet"/>
	<link href="/css/login.css" type="text/css" rel="stylesheet"/>
	<link href="/css/signup.css" type="text/css" rel="stylesheet"/>
	<link href="/css/ring_indicator.css" type="text/css" rel="stylesheet"/>
	<link href="/css/navigation.css" type="text/css" rel="stylesheet"/>
	<script type="text/javascript" src="/scripts/buttons.js"></script>
	<script type="text/javascript" src="/scripts/join.js"></script>
	<script type="text/javascript" src="/scripts/login.js"></script>
	<script type="text/javascript">
		function checkUsername(){
			let username = document.getElementById("username");
			if(username.value.length === 0){
				username.setAttribute("invalid", true);
			} else {
				username.setAttribute("invalid", false);
			}
			checkInfo();
		}

		function checkPassword(){
			let password = document.getElementById("password");
			let value = password.value;

			if(value.length < 8){
				password.setAttribute("invalid", true);
			}

			let password_number_regex = /.*\d.*/;
			if(!password_number_regex.test(value)){
				password.setAttribute("invalid", true);
			}

			let upper_regex = /.*[A-Z].*/;
			if(!upper_regex.test(value)){
				password.setAttribute("invalid", true);
			}

			let lower_regex = /.*[a-z].*/;
			if(!lower_regex.test(value)){
				password.setAttribute("invalid", true);
			}

			let symbol_regex = /.*[!#$@%()^&;:-].*/;
			if(!symbol_regex.test(value)){
				password.setAttribute("invalid", true);
			} else{
				password.setAttribute("invalid", false);
			}

			checkInfo();
		}

		function checkInfo(){
			let username = document.getElementById("username");
			let password = document.getElementById("password");

			if(username.value.length === 0 || password.value.length < 8){
				missingInfo();
			} else{
				allGood();
			}
		}
	</script>
</head>
<body>
<!-- NAVIGATION STARTS HERE -->
	<nav>
		<ul class="navigation">
			<div class="brand"> 
	<!-- Making menu icon clickable to display the navigation menu on smaller screens -->
				<i onclick="navToggle()" id="nav-icon" class="fa fa-navicon" style="font-size:24px"></i> 
			</div>
			 <a href="/" class="w3-bar-item w3-button w3-wide">
				<img class="img-nav" src="/images/logo_square.png" alt="WCS">
			</a>
		</ul>
	</nav>

	<section class="form center">
		<div class="center">
			<fieldset>
				<label for="username">Username</label>
				<input type="text" id="username" name="username" value="" autocomplete="username" placeholder = "Username" oninput="checkUsername()" required>
			</fieldset>
			<fieldset>
				<label for="password">Password</label>
				<input type="password" id="password" name="password" value="" autocomplete="current-password" placeholder = "Password" oninput="checkInfo()" onkeyup="checkInfo()" required>
			</fieldset>
			<fieldset>
				<label for="auth_code">2FA code</label>
				<input type="number" id="auth_code" name="auth_code" value="" placeholder="2FA code"><br>
			</fieldset>

			<div class="" id="submit_wrapper">
				<button type="submit" name="submit" id="submit" form="login_form" onclick="login()" hidden>Login</button>
			</div>
			<p>Not with us yet?<a href="/signup.php"> Sign Up Here</a></p>
			<div id="server_response" hidden></div>
		</div>
	</section>
</body>
</html>