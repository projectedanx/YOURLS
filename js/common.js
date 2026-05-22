// Handle .hide-if-no-js and .hide-if-js styles
$(document).ready(function(){
	$('.hide-if-no-js').removeClass('hide-if-no-js');
 	$('.hide-if-js').hide();
});

// Change an element text an revert in a smooth pulse. el is an element id like '#copybox h2'
function html_pulse( el, newtext ){
	const oldtext = $(el).html();
	// Fast pulse to "Copied" and revert
	$(el).fadeTo(
		"normal",
		0.01,
		function(){
			$(el)
			.html( newtext )
			.css('opacity', 1)
			.fadeTo(
				"slow", 1, // this fades from 1 to 1: just a 'sleep(1)' actually
				function(){
					$(el).fadeTo("normal", 0.01, function(){$(el).html( oldtext ).css('opacity', 1)});
				}
			);
		}
	);


}

// Update feedback message
function feedback(msg, type, delay) {
	const closeme = ( type === 'fail' || type === 'error' ) ? true : false;
	delay = delay || ( closeme === true ? 10000 : 3500 );
	$.notifyBar({
		html: '<span>'+msg+'</span>',
		delay: delay,
		animationSpeed: "normal",
		close: closeme,
		cls: type
	});
	return true;
}

// Unused for now
function logout() {
	$.ajax({
		type: "POST",
		url: ajaxurl,
		data: {action:'logout'},
		success: function() {
			window.parent.location.href = window.parent.location.href;
		}
	});
}

// Begin the spinning animation & disable a button
function add_loading(el) {
	$(el).attr("disabled", "disabled").addClass('disabled').addClass('loading');
}

// End spinning animation
function end_loading(el) {
	$(el).removeClass('loading');
}

// Un-disable an element
function end_disable(el) {
	$(el).removeAttr("disabled").removeClass('disabled');
}

// Trim long string
function trim_long_string( string, length = 60 ) {
	let newstring = string;
	if ( newstring.length > length ) {
		newstring = newstring.substr(0, (length - 5) ) + '[...]';	
	}
	return newstring;
}

// Get the var=value from a query string
function get_var_from_query( url, varname, default_val ) {
	if( varname === undefined ) {
		varname = 'nonce';
	}
	if( default_val === undefined ) {
		default_val = '';
	}

	// Split the url on '?' and get only the params (which is element 1)
	let parts = url.split('?');
	if (parts.length < 2) return default_val;
	let query = parts[1];

	// Now split those params on '&' so we can get each one individually (Ex. param_var=param_value)
	let params = query.split('&');

	for( let i=0; i<params.length; i++ ){
		// So split the first param elemment on '=' and check the param_var to see if it matches varname (element 0)
		let keyval = params[i].split('=');
		if( keyval[0] === varname ){
			// If it matches we want to return the param_value
			return keyval[1];
		}
	}
	
	// If we didn't find anything then we just return the default_val
	return default_val;	
}

/**
 * Jquery Cookie plugin
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * Available at http://plugins.jquery.com/files/jquery.cookie.js.txt
 */
jQuery.cookie = function(name, value, options) {
    if (typeof value !== 'undefined') { // name and value given, set cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        let expires = '';
        if (options.expires && (typeof options.expires === 'number' || options.expires.toUTCString)) {
            let date;
            if (typeof options.expires === 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        const path = options.path ? '; path=' + (options.path) : '';
        const domain = options.domain ? '; domain=' + (options.domain) : '';
        const secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        let cookieValue = null;
        if (document.cookie && document.cookie !== '') {
            const cookies = document.cookie.split(';');
            for (let i = 0; i < cookies.length; i++) {
                const cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) === (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};

// Split a URL into protocol, slashes and the rest
function get_protocol_slashes_and_rest( url ) {
	let ups = url.match( /^[a-zA-Z0-9\+\.-]+:(\/\/)?/ );
	if( ups ) {
		const matched_ups = ups[0];
		const ur = url.split(new RegExp(matched_ups))[1];
		const ups_parts = matched_ups.split(/\:/);
		return { protocol: ups_parts[0]+':', slashes: ups_parts[1], rest: ur };
	} else {
		return { protocol: '', slashes: '', rest: url };
	}
}