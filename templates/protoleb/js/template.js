/**
 * @package     Joomla.Site
 * @subpackage  Templates.protostar
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       3.2
 */

(function ( $ ) {
	$(document).ready(function () {
		
		/* sortable 20,50,all spike-nail */
		var replacePhrase = 9998, allPhrase = "Все";
		jQuery("#limit option").each(function () {
			if ( jQuery(this).text() == replacePhrase ) {
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
		
		if ( jQuery.fn.fancybox ) {
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
			
			if ( !input.prop('checked') ) {
				label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
				if ( input.val() == '' ) {
					label.addClass('active btn-primary');
				} else if ( input.val() == 0 ) {
					label.addClass('active btn-danger');
				} else {
					label.addClass('active btn-success');
				}
				input.prop('checked', true);
			}
		});
		$(".btn-group input[checked=checked]").each(function () {
			if ( $(this).val() == '' ) {
				$("label[for=" + $(this).attr('id') + "]").addClass('active btn-primary');
			} else if ( $(this).val() == 0 ) {
				$("label[for=" + $(this).attr('id') + "]").addClass('active btn-danger');
			} else {
				$("label[for=" + $(this).attr('id') + "]").addClass('active btn-success');
			}
		});
	});
	
	function setEqualHeight( columns ) {
		var tallestcolumn = 0;
		columns.each(function () {
			//var currentHeight;
			currentHeight = $(this).height();
			if ( currentHeight > tallestcolumn ) {
				tallestcolumn = currentHeight;
			}
		});
		columns.height(tallestcolumn);
	}
	
})(jQuery);