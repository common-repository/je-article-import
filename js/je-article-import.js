/*
Plugin Name: JE Article Import
Plugin URI: http://www.tsvurach-handball.de
Description: Import articles from a custom user site
Version: 0.1
Author: Jens Ertel
Author URI: http://www.tsvurach-handball.de
License: GPLv2
*/

var i = 1000; /* Set Global Variable i */
function increment(){
	i += 1; /* Function for automatic increment of field's "Name" attribute. */
}

(function($){
	$(document).ready(function(){
		$('.customaddmedia').click(function(e){
			var $el = $(this).parent();
			e.preventDefault();
			// console.log('test');
			var uploader = wp.media({
				title : 'Bild auswählen',
				button : {
					text : 'Bild verwenden'
				},
				multiple : false
			})
			.on('select', function(){
				var selection = uploader.state().get('selection');
				var attachment = selection.first().toJSON();
				console.log(attachment);
				$('.customaddmediaid', $el).val(attachment.id);
				$('.customaddmediaurl', $el).val(attachment.url);
				$('.customaddmediaimg', $el).attr('src', attachment.url);
			})
			.open();
		})
		$('.customaddmediaclear').click(function(e){
			var $el = $(this).parent();
			e.preventDefault();
			$('.customaddmediaid', $el).val('');
			$('.customaddmediaurl', $el).val('');
			$('.customaddmediaimg', $el).attr('src', '');
		})
	})	
})(jQuery);

function je_ai_setting_mailreciptient(){
	var r = document.createElement('td');
	var y = document.createElement("INPUT");
	y.setAttribute("type", "text");
	y.setAttribute("placeholder", "E-Mail");
	//var g = document.createElement("IMG");
	//g.setAttribute("src", "delete.png");
	increment();
	y.setAttribute("Name", "je_ai_setting_mailreciptient_" + i);
	r.appendChild(y);
	// g.setAttribute("onclick", "removeElement('myForm','id_" + i + "')");
	//r.appendChild(g);
	//r.setAttribute("id", "id_" + i);
	document.getElementById("myForm").appendChild(r);
}

function je_ai_setting_mailreciptient_remove(dId) {
	var ni = document.getElementById(dId);
	ni.parentNode.removeChild(document.getElementById(dId));
	return false;
}