function tinyFeedback(c){var b=true;jQuery.getJSON(c+"tinyFeedback.php",{config:true},function(j){config=j;if(config.cookie_enabled==="1"){function k(p){var n=document.cookie.split(";");for(var m=0;m<n.length;m++){var o=n[m];while(o.charAt(0)==" "){o=o.substring(1,o.length)}if(o.indexOf(p)==0){return unescape(o.substring(p.length+1,o.length))}}}var g=k("tinyFeedback");var i=document.location.pathname+document.location.search+",";if(g&&g.indexOf(i)!=-1){b=false}}if(b){var h=jQuery("<div/>",{id:"tinyFeedbackWrap"});var f=jQuery("<div/>",{html:"<p>"+config.widget_text+"</p>",id:"tinyFeedback"});var e=jQuery("<a/>",{html:config.widget_yes,id:"yes",click:function(){a(f)}}).appendTo(f);var l=jQuery("<a/>",{html:config.widget_no,id:"no",click:function(){d(f)}}).appendTo(f);f.appendTo(h);h.appendTo(config.widget_target)}});function a(e){jQuery.post(c+"handleFeedback.php",{type:"positive"});if(config.analytics_enabled==="1"){_gaq.push(["_trackEvent","Feedback","Positive",document.location.href])}e.html("<p>"+config.widget_thankyou+"</p>").delay(1000).fadeOut("slow",function(){e.remove()})}function d(g){jQuery.post(c+"handleFeedback.php",{type:"negative"});if(config.analytics_enabled==="1"){_gaq.push(["_trackEvent","Feedback","Negative",document.location.href])}var f=jQuery("<div/>",{id:"lb_overlay"});var e=jQuery("<a/>",{html:"X",id:"lb_close",click:function(){j()}});var k=jQuery("<div/>",{html:e,id:"tinyFeedbackNegative"});var i=jQuery("<div/>",{html:config.form_text,id:"tinyFeedbackNegativeText"});var h=jQuery("<form/>",{html:"<h3>"+config.form_caption+'</h3><input type="text" name="email" id="email" placeholder="'+config.form_email_placeholder+'" tabindex="1"><textarea name="feedback" id="feedback" placeholder="'+config.form_textarea_placeholder+'" tabindex="2"></textarea><input type="submit" id="btn_sendfeedback" value="'+config.form_send_button_text+'" tabindex="3"><p class="tiny">HTML not allowed.</p>',action:"#",method:"post",id:"tinyFeedbackForm"});function j(){k.fadeOut("fast",function(){k.remove();f.remove()})}h.submit(function(l){l.preventDefault();if(l.target[1].value!=""){jQuery.post(c+"handleFeedback.php",{type:"written",email:l.target[0].value,feedback:l.target[1].value},function(m){i.html(m);k.delay(2000).fadeOut("slow",function(){k.remove();f.remove();g.html("<p>"+config.widget_thankyou+"</p>").delay(1000).fadeOut("slow",function(){g.remove()})})})}});jQuery(document).keydown(function(l){if(l.which==27){j()}});jQuery(config.widget_target).append(f);i.appendTo(k);h.appendTo(k);k.appendTo(jQuery(config.widget_target));k.fadeIn("slow");jQuery("#feedback").focus()}};
