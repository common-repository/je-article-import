/*
Plugin Name: JE Article Import
Plugin URI: http://www.tsvurach-handball.de
Description: Import articles from a custom user site
Version: 0.1
Author: Jens Ertel
Author URI: http://www.tsvurach-handball.de
License: GPLv2
*/

(function($){
	$(document).ready(function() {
		$('.input_header').keyup(function(){
			var max = parseInt($(this).attr('maxlength'));
			if($(this).val().length > max){
				$(this).val($(this).val().substr(0, max));
			}
			$(this).parent().find('.input_header_left').html(max - $(this).val().length);
		});
		$('.input_author').keyup(function(){
			var max = parseInt($(this).attr('maxlength'));
			if($(this).val().length > max){
				$(this).val($(this).val().substr(0, max));
			}
			$(this).parent().find('.input_author_left').html(max - $(this).val().length);
		});
		$('.input_body').keyup(function(){
			var max = parseInt($(this).attr('maxlength'));
			if($(this).val().length > max){
				$(this).val($(this).val().substr(0, max));
			}
			$(this).parent().find('.input_body_left').html(max - $(this).val().length);
		});
		$('.submit_send').click(function(e){
			var a=document.forms["frm_article"]["str_header"].value;
			var b=document.forms["frm_article"]["str_author"].value;
			var c=document.forms["frm_article"]["str_body"].value;
			if (a==null || a=="")
			{
				alert("Es muss ein Titel eingegeben werden");
				return false;
			}
			if (b==null || b=="")
			{
				alert("Es muss ein Autor eingegeben werden");
				return false;
			}
			if (c==null || c=="")
			{
				alert("Es muss ein Bericht eingegeben werden");
				return false;
			}
		})
		$('.submit_reset').click(function(){
			var max = parseInt($('.input_header').attr('maxlength'));
			if($('.input_header').val().length > max){
				$('.input_header').val($('.input_header').val().substr(0, max));
			}
			$('.input_header').parent().find('.input_header_left').html(max - $('.input_header_left').val().length);
			
			var max = parseInt($('.input_author').attr('maxlength'));
			if($('.input_author').val().length > max){
				$('.input_author').val($('.input_author').val().substr(0, max));
			}
			$('.input_author').parent().find('.input_author_left').html(max - $('.input_author_left').val().length);
			
			var max = parseInt($('.input_body').attr('maxlength'));
			if($('.input_body').val().length > max){
				$('.input_body').val($('.input_body').val().substr(0, max));
			}
			$('.input_body').parent().find('.input_body_left').html(max - $('.input_body_left').val().length);
		});
	});
})(jQuery);