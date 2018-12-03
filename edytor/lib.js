//jako że nie planujemy dołożyć biblioteki jQuery, a ja jestem leniwy i nie lubię dużo pisać, to taka jakby moja mini biblioteka - PT
function $create(el_id, el_class, el_text, el_type){
	if(!el_type)
		el_type = 'div';
		
	var el = document.createElement(el_type);
	
	if(el_id)
		el.id = el_id;
	if(el_class)
		el.className = el_class;
	if(el_text)
		el.innerHTML = el_text;
		
	return el;
}

function $get(selector){
	var selector_arr = selector.split(' ');
	var ancestor = document;
	for(var i = 0; i < selector_arr.length; i++){
		selector = selector_arr[i];
		if(selector[0] == '#')
			ancestor = ancestor.getElementById(selector.slice(1));
		else if(selector[0] == '.')
			ancestor = ancestor.getElementsByClassName(selector.slice(1));
	}
	return ancestor;
	//jakby chcieć używać innych selektorów to trzeba tu dorzucić, obecnie nie obsługuje czegoś w klasie np. ('.class1 .class2')
}

function $setAtr(el,attr, value){
	el.setAttribute("data-" + attr, value);
	return;
}

function $getAtr(el, attr){
	return el.getAttribute("data-" + attr);
}

function $ajax(url, command, onReturn, variable, show){
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			onReturn(this,variable,show);
		}
	};
	xhttp.open("GET", url + command, true);
	xhttp.send();
	return;
}

function $findClass(el, el_class){
	return el.classList != undefined && el.classList.contains(el_class);
}

function $addClass(el, el_class){
	if($findClass(el, el_class))
		return false;
	el.classList.add(el_class);
	return true;
}

function $removeClass(el, el_class){
	if(!$findClass(el, el_class))
		return false;
	el.classList.remove(el_class);
	return true;
}