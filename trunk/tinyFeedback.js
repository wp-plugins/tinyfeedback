function tinyFeedback(path) {
	var display = true;

	jQuery.getJSON(path + 'tinyFeedback.php', { 'config':true }, function(data) {
		config = data;
		// Cookie check
		if(config['cookie_enabled'] === '1') {
			function readCookie(cookieName) {
				var cookies = document.cookie.split(';');
				for(var i=0; i<cookies.length; i++) {
					var c = cookies[i];
					while(c.charAt(0) == ' ') {
						c = c.substring(1, c.length);
					}
					if(c.indexOf(cookieName) == 0) {
						return unescape(c.substring(cookieName.length+1, c.length));
					}
				}
			}
			var cookie = readCookie('tinyFeedback');
			var thisPage = document.location.pathname + document.location.search + ',';

			if(cookie && cookie.indexOf(thisPage) != -1) {
				display = false;
			}
		}

		if(display) {
			var wrap = jQuery('<div/>', { 'id': 'tinyFeedbackWrap'});
			var cont = jQuery('<div/>', { html: '<p>' + config['widget_text'] + '</p>', 'id': 'tinyFeedback'});
			var btnYes = jQuery('<a/>', { html: config['widget_yes'], 'id': 'yes', click: function() { feedbackYes(cont); } }).appendTo(cont);
			var btnNo = jQuery('<a/>', { html: config['widget_no'], 'id': 'no', click: function() { feedbackNo(cont); } }).appendTo(cont);
			cont.appendTo(wrap);
			wrap.appendTo(config['widget_target']);
		}
	});

	function feedbackYes(cont) {
		jQuery.post(path + 'handleFeedback.php', { 'type': 'positive' });
		if(config['analytics_enabled'] === '1') {
			_gaq.push(['_trackEvent', 'Feedback', 'Positive', document.location.href]);
		}
		cont.html('<p>' + config['widget_thankyou'] + '</p>').delay(1000).fadeOut('slow', function() { cont.remove(); });
	}

	function feedbackNo(cont) {
		jQuery.post(path + 'handleFeedback.php', { 'type': 'negative' });
		if(config['analytics_enabled'] === '1') {
			_gaq.push(['_trackEvent', 'Feedback', 'Negative', document.location.href]);
		}
		var lb_overlay = jQuery('<div/>', { 'id': 'lb_overlay' });
		var closelink = jQuery('<a/>', { html: 'X', 'id': 'lb_close', click: function() { removeLB(); } });
		var lb_cont = jQuery('<div/>', { html: closelink, 'id': 'tinyFeedbackNegative' });
		var lb_text = jQuery('<div/>', { html: config['form_text'], 'id': 'tinyFeedbackNegativeText' });
		var form = jQuery('<form/>', { html: '<h3>' + config['form_caption'] + '</h3><input type="text" name="email" id="email" placeholder="' + config['form_email_placeholder'] + '" tabindex="1"><textarea name="feedback" id="feedback" placeholder="' + config['form_textarea_placeholder'] + '" tabindex="2"></textarea><input type="submit" id="btn_sendfeedback" value="' + config['form_send_button_text'] + '" tabindex="3"><p class="tiny">HTML not allowed.</p>', 'action': '#', 'method': 'post', 'id': 'tinyFeedbackForm' });

		function removeLB() {
			lb_cont.fadeOut('fast', function() {
				lb_cont.remove();
				lb_overlay.remove();
			});
		}

		form.submit(function(event) {
			event.preventDefault();
			if(event.target[1].value != '') {
				jQuery.post(path + 'handleFeedback.php', { 'type': 'written', 'email': event.target[0].value, 'feedback': event.target[1].value }, function(data) {
					lb_text.html(data);
					lb_cont.delay(2000).fadeOut('slow', function() { 
						lb_cont.remove(); 
						lb_overlay.remove();
						cont.html('<p>' + config['widget_thankyou'] + '</p>').delay(1000).fadeOut('slow', function() { cont.remove(); });
					});
				});
			}
		});

		jQuery(document).keydown(function(e) {
			if(e.which == 27) {
				removeLB();
			}
		});

		jQuery(config['widget_target']).append(lb_overlay);
		lb_text.appendTo(lb_cont);
		form.appendTo(lb_cont);
		lb_cont.appendTo(jQuery(config['widget_target']));
		lb_cont.fadeIn('slow');
		jQuery('#feedback').focus();
	}
}
