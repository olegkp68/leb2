// Данная функция создаёт кроссбраузерный объект XMLHTTP

function getXmlHttp() {
	try {
		return new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
			return new ActiveXObject("Microsoft.XMLHTTP");
		} catch (ee) {
		}
	}
	if (typeof XMLHttpRequest != 'undefined') {
		return new XMLHttpRequest();
	}
}

function getUrl() {

	let xhr = getXmlHttp(); // Создаём объект XMLHTTP


	let url = '..\\plugins\\index.php?option=com_ajax&plugin=carthighlite&group=ajax&format=json';

	xhr.open('GET', url); // Открываем асинхронное соединение
	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
	// xhr.setRequestHeader('Content-Type', 'application/json');
	// xhr.responseType = 'json';

	xhr.send(null);

	// xhr.send("a=" + encodeURIComponent(a) + "&b=" + encodeURIComponent(b)); // Отправляем POST-запрос

	xhr.onload = () => {
		if (xhr.status == 200) { // если статус не 200, то произошла ошибка
			cart_highlite(xhr.response);
		}
	};



}

function cart_highlite(response) {

	var nums = JSON.parse(response);
	nums = JSON.parse(nums.data);

	for (let num of nums) {
		// console.log(num);
		for (let elem of document.querySelectorAll(`span[class*="idp-"] input`)) {
			if (elem.matches(`span[class*="idp-${num}"] input`)) {
				elem.value = "В корзине";
				elem.title = "В корзине";
				if (!elem.classList.contains('cart_highlite')) {
					elem.classList.add('cart_highlite');

				}
				break;
			}
		}
	}
}


document.addEventListener('click', function (event) {
	// console.log(event.target);
	let target = event.target;
	if (target.id == "fancybox-close") {
		getUrl();
	}

});
addEventListener('unload', getUrl());

// var e = document.querySelector(".yt-button.yt-button_type_left");
// e.addEventListener("click", ddr, false);

