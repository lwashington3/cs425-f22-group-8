function login_listener(){
	let headers = parse_response_headers(this.getAllResponseHeaders());

	if("response" in headers){
		let error = document.getElementById("server_response");

		error.hidden = false;
		error.innerText = headers["response"];

		return;
	}

	//window.location = headers["location"];
}

function login(){
	let username = document.getElementById("username").value;
	let password = document.getElementById("password").value;
	let auth_code = document.getElementById("auth_code").value;

	const req = new XMLHttpRequest();
	req.addEventListener("load", login_listener);
	req.open("POST", "https://wcs.lenwashingtoniii.com/api/login.php", true);
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(`username=${username}&password=${password}&auth_code=${auth_code}`);
	req.onreadystatechange = () => {
		if(req.status >= 400){
			let div = document.querySelector("div#server_response");
			div.hidden = false;
			div.value = req.getResponseHeader("Response");
		} else{
			window.location.href = req.getResponseHeader("Location");
		}
	}
}

function parse_response_headers(headers){
	const arr = headers.trim().split(/[\r\n]+/);

	const dct = {};

	arr.forEach((line) => {
		const parts = line.split(": ");
		const header = parts.shift();
		dct[header] = parts.join(': ');
	});

	return dct;
}