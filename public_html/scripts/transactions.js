function parseMoney($raw){
	return parseFloat($raw).toLocaleString('en-US',{ style: 'currency', currency: 'USD' });
}

function accountRowOnClick(row){
	let account_number = row["id"];
	account_number = /account([\w\d-]+)/.exec(account_number)[1];
	if(account_number !== document.getElementById("number").innerText){
		showAccount(account_number);
	} else{
		closeSidebar();
	}
}

function showAccount(account_number){
	let params = `account_number=${account_number}`;
	const req = new XMLHttpRequest();
	req.addEventListener("load", reqListener);
	req.open("POST", "https://wcs.lenwashingtoniii.com/api/get_account_info.php");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(params);
	openSidebar();
}

function reqListener() {
	let json = JSON.parse(this.responseText);
	let dct = {
		"Balance": "balance",
		"Interest": "interest",
		"Monthly Fee": "monthly_fees",
		"Name": "name",
		"Overdrawn": "overdrawn",
		"Account Number": "number"
		//"Type": "",
	};
	json["Overdrawn"] = json["Overdrawn"] ? "Yes" : "No";
	json["Balance"] = parseMoney((json["Balance"]));
	json["Monthly Fee"] = parseMoney(json["Monthly Fee"]);
	json["Interest"] = `${json["Interest"]}%`;

	let keys = Object.keys(dct);
	for(let i = 0; i < keys.length; i++){
		let key = keys[i];
		document.getElementById(dct[key]).innerText = json[key];
	}
}

function checkTransactionType(){
	let type = document.getElementById("transaction");
	let transfer_account = document.getElementById("transfer_to_account_number");
	let line_break = document.getElementById("transfer_break");
	if(type.value === "Transfer"){
		transfer_account.hidden = line_break.hidden = false;
		transfer_account.required = true;
	} else{
		transfer_account.hidden = line_break.hidden = true;
		transfer_account.required = false;
	}
}

function transactionListener() {
	alert(this.responseText);
	if(this.status !== 200){
		return;
	}

	let affected_rows = JSON.parse(this.getResponseHeader("Affected-Rows"));
	for(let i = 0; i < affected_rows; i++){
		// TODO: Update the individual rows
	}
	getAccounts();
}

function transact(){
	let transaction_type = document.getElementById("transaction").value;
	let amount = document.getElementById("amount").value;
	let this_account = document.getElementById("number").innerText;
	let description = document.getElementById("description").value;
	let params = `transaction_type=${transaction_type}&amount=${encodeURIComponent(amount)}&description=${encodeURIComponent(description)}&`;
	if(transaction_type === "Transfer"){
		let final_account = document.getElementById("transfer_to_account_number").value;
		params += `initial_account=${this_account}&final_account=${final_account}`;
	} else{
		params += `account_number=${this_account}`;
	}
	const req = new XMLHttpRequest();
	req.addEventListener("load", transactionListener);
	req.open("POST", "https://wcs.lenwashingtoniii.com/api/transact.php");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(params)
}

function loadSchedule(){
	let json = JSON.parse(this.responseText);
	let table = document.getElementById("schedule");

	while(table.lastElementChild !== table.firstElementChild){
		table.removeChild(table.lastElementChild);
	}

	for(let i = 0; i < json.length; i++){
		let row = json[i];
		let tr = document.createElement("tr");

		let balance = parseMoney(row["account_balance"]);
		let amount = parseMoney(row["transaction_amount"]);
		let time = new Date(Date.parse(row["day"])).toLocaleString();

		tr.innerHTML = `<td>${time}</td><td style="text-align: right">${amount}</td><td style="text-align: right">${balance}</td><td>${row["transaction_description"]}</td>`;
		table.appendChild(tr);
	}
}

function getPendingTransactions(){
	let account_number = document.getElementById("number").innerText;

	const req = new XMLHttpRequest();
	req.addEventListener("load", loadSchedule);
	req.open("POST", "https://wcs.lenwashingtoniii.com/api/get_pending_transactions.php");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(`account_number=${account_number}`);
}

function getMonthlyStatement(){
	let month = document.getElementById("statement_month").value;
	let account_number = document.getElementById("number").innerText;

	if(month === ""){
		alert("You must input a month and year to see its monthly report.");
		return;
	}

	const req = new XMLHttpRequest();
	req.addEventListener("load", loadSchedule);
	req.open("POST", "https://wcs.lenwashingtoniii.com/api/get_monthly_statement.php");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(`account_number=${account_number}&statement_month=${month}`);
}

function accountListener(){
	let json = JSON.parse(this.responseText);

	let tr = document.createElement("tr");

	let number = json["Account Number"];
	let name = json["Name"];
	let balance = parseMoney(json["Balance"]);
	let type = json["Type"];
	let interest = json["Interest"];
	let monthly_fee = parseMoney(json["Monthly Fee"]);
	let overdrawn = json["Overdrawn"] ? "Yes" : "No";

	tr.id = `account${number}`;
	tr.onclick = () => accountRowOnClick(tr);
	tr.innerHTML = `<td>${name}</td><td style="text-align:right">${balance}</td><td>${type}</td><td style="text-align: right">${interest}%</td><td style="text-align:right">${monthly_fee}</td><td style="text-align: center">${overdrawn}</td>`;
	document.getElementById("accounts").appendChild(tr);
}


function displayAccount(account_number){
	let params = `account_number=${account_number}`;
	const req = new XMLHttpRequest();
	req.addEventListener("load", accountListener);
	req.open("POST", "https://wcs.lenwashingtoniii.com/api/get_account_info.php");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(params);
}

function getAccounts(){
	let table = document.getElementById("accounts");
	while(table.lastElementChild !== table.firstElementChild){
		table.removeChild(table.lastElementChild);
	}

	const req = new XMLHttpRequest();

	function _thisListener(){
		let json = JSON.parse(this.responseText);
		for(let i = 0; i < json.length; i++){
			displayAccount(json[i]);
		}
	}

	req.addEventListener("load", _thisListener);
	req.open("POST", "https://wcs.lenwashingtoniii.com/api/get_accounts.php");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send();
}

function listener(){
	if(this.status !== 200){
		return;
	}
	alert(this.responseText);
	displayAccount(this.getResponseHeader("Account-Number"))
}

function createAccount(){
	const form = document.forms.create_account_form;
	const name = encodeURIComponent(form.elements.account_name.value);
	const type = form.elements.account_type.value;
	const initial = form.elements.initial_balance.value;

	if(initial < 0){
		alert("The initial amount of an account can not be negative.");
		return;
	}

	const req = new XMLHttpRequest();
	req.addEventListener("load", listener);
	req.open("POST", "https://wcs.lenwashingtoniii.com/api/create_account.php");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(`account_name=${name}&account_type=${type}&initial_balance=${initial}`);
}

function deleteListener(){
	alert(this.responseText);
	if(this.status !== 200){
		return;
	}
	let deleted_account = this.getResponseHeader("Deleted-Account-Number");
	let table = document.getElementById("accounts");
	for(let i = 0; i < table.children.length; i++){
		let child = table.children[i];
		if(child.id === `account${deleted_account}`){
			table.removeChild(child);
			return;
		}
	}
}

function deleteAccount(){
	const name = document.getElementById("name").innerText;
	const number = document.getElementById("number").innerText;
	let confirmation = prompt(`Deleting an account is an irreversible action, but the transactions of the account will remain. If you understand, please type the account name: "${name}".`)
	if(confirmation !== name){
		return;
	}
	const req = new XMLHttpRequest();
	req.addEventListener("load", deleteListener);
	req.open("POST", "https://wcs.lenwashingtoniii.com/api/delete_account.php");
	req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	req.send(`account_number=${number}`);
}