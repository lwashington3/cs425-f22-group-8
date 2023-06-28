function processInput(holder){
	var elements = holder.children(), //taking the "kids" of the parent
		str = ""; //unnecesary || added for some future mods

	elements.each(function(e){ //iterates through each element
		var val = $(this).val().replace(/\D/,""), //taking the value and parsing it. Returns string without changing the value.
			focused = $(this).is(":focus"), //checks if the current element in the iteration is focused
			parseGate = false;

		val.length==1?parseGate=false:parseGate=true;
		/*a fix that doesn't allow the cursor to jump
		to another field even if input was parsed
		and nothing was added to the input*/

		$(this).val(val); //applying parsed value.

		if(parseGate&&val.length>1){ //Takes you to another input
			var	exist = elements[e+1]?true:false; //checks if there is input ahead
			exist&&val[1]?( //if so then
				elements[e+1].disabled=false,
					elements[e+1].value=val[1], //sends the last character to the next input
					elements[e].value=val[0], //clears the last character of this input
					elements[e+1].focus() //sends the focus to the next input
			):void 0;
		} else if(parseGate&&focused&&val.length==0){ //if the input was REMOVING the character, then
			var exist = elements[e-1]?true:false; //checks if there is an input before
			if(exist) elements[e-1].focus(); //sends the focus back to the previous input
		}

		val==""?str+=" ":str+=val;
	});
}

$("#inputs").on('input', () => processInput($(this))); //still wonder how it worked out. But we are adding input listener to the parent... (omg, jquery is so smart...);

$("#inputs").on('click', function(e) { //making so that if human focuses on the wrong input (not first) it will move the focus to a first empty one.
	var els = $(this).children(),
		str = "";
	els.each(function(e){
		var focus = $(this).is(":focus");
		$this = $(this);
		while($this.prev().val()==""){
			$this.prev().focus();
			$this = $this.prev();
		}
	})
});