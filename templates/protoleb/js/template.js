/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       3.2
 */


(function ($) {
	$(document).ready(function () {

		/* sortable 20,50,all spike-nail */
		var replacePhrase = 9998, allPhrase = "Все";
		jQuery("#limit option").each(function () {
			if (jQuery(this).text() == replacePhrase) {
				//console.log(replacePhrase + ": true");
				jQuery(this).text(allPhrase);
			}
		});

		/* setEqualHeight */
		setEqualHeight($('.product-field-type-R'));

		/* remove titles in registration form */
		$('#member-registration .control-group').each(function () {
			$label = $(this).find('label');
			$label.removeAttr("title");
		});

		/* cut ORDER NUMBER  post oprder */
		var $orderNumber = $('.post_payment_order_number_number');
		$orderNumber.slice(4);

		if (jQuery.fn.fancybox) {
			$(".fancybox").fancybox({
				//beforeShow: function () {},
				//afterClose: function () {}
			});
		}

		//$('*[rel=tooltip]').tooltip({
		//	position: {
		//		my   : "center top+20",
		//		at   : "center top",
		//		track: true,
		//		hide : { effect: "fold", duration: 100 }
		//	}
		//});

		// Turn radios into btn-group
		$('.radio.btn-group label').addClass('btn');
		$(".btn-group label:not(.active)").click(function () {
			var label = $(this),
				input = $('#' + label.attr('for'));

			if (!input.prop('checked')) {
				label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
				if (input.val() == '') {
					label.addClass('active btn-primary');
				} else if (input.val() == 0) {
					label.addClass('active btn-danger');
				} else {
					label.addClass('active btn-success');
				}
				input.prop('checked', true);
			}
		});
		$(".btn-group input[checked=checked]").each(function () {
			if ($(this).val() == '') {
				$("label[for=" + $(this).attr('id') + "]").addClass('active btn-primary');
			} else if ($(this).val() == 0) {
				$("label[for=" + $(this).attr('id') + "]").addClass('active btn-danger');
			} else {
				$("label[for=" + $(this).attr('id') + "]").addClass('active btn-success');
			}
		});
	});

	function setEqualHeight(columns) {
		var tallestcolumn = 0;
		columns.each(function () {
			//var currentHeight;
			currentHeight = $(this).height();
			if (currentHeight > tallestcolumn) {
				tallestcolumn = currentHeight;
			}
		});
		columns.height(tallestcolumn);
	}

})(jQuery);


// Данная функция создаёт кроссбраузерный объект XMLHTTP

// function getXmlHttp() {
// 	try {
// 		return new ActiveXObject("Msxml2.XMLHTTP");
// 	} catch (e) {
// 		try {
// 			return new ActiveXObject("Microsoft.XMLHTTP");
// 		} catch (ee) {
// 		}
// 	}
// 	if (typeof XMLHttpRequest != 'undefined') {
// 		return new XMLHttpRequest();
// 	}
// }

// function getUrl() {

// 	let xhr = getXmlHttp(); // Создаём объект XMLHTTP


// 	let url = '..\\plugins\\index.php?option=com_ajax&plugin=carthighlite&group=ajax&format=json';
// 	// let url = '..\\plugins\\index.php?option=com_ajax&plugin=latestarticles&group=ajax&format=json';
// 	console.log(url);
// 	xhr.open('GET', url); // Открываем асинхронное соединение
// 	xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=utf-8');
// 	// xhr.setRequestHeader('Content-Type', 'application/json');
// 	// xhr.responseType = 'json';
// 	xhr.send();

// 	// xhr.send("a=" + encodeURIComponent(a) + "&b=" + encodeURIComponent(b)); // Отправляем POST-запрос
// 	xhr.onload = () => {
// 		if (xhr.status == 200) { // анализируем HTTP-статус ответа, если статус не 200, то произошла ошибка
// 			// console.log(xhr.response);
// 			cart_highlite(xhr.response);

// 		}
// 	};



// }

// function cart_highlite(response) {

// 	//	elems = document.querySelectorAll(`span[class*="idp-"] input`);

// 	var nums = JSON.parse(response);
// 	nums = JSON.parse(nums.data);
// 	console.log(nums);

// 	//	let elem = document.querySelector(`span[class*="idp-${num}"] input`);
// 	//	elem.style.background = '#000';
// 	for (let num of nums) {
// 		console.log(num);
// 		for (let elem of document.querySelectorAll(`span[class*="idp-"] input`)) {
// 			if (elem.matches(`span[class*="idp-${num}"] input`)) {
// 				elem.value = "В корзине";
// 				if (!elem.classList.contains('cart_highlite')) {
// 					elem.classList.add('cart_highlite');
// 				}
// 				break;
// 			}
// 		}
// 	}




// }

// addEventListener('unload', getUrl());
