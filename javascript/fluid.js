String.prototype.trim = function() {
    return this.replace(/^\s+|\s+$/g,"");
}
String.prototype.ltrim = function() {
    return this.replace(/^\s+/,"");
}
String.prototype.rtrim = function() {
    return this.replace(/\s+$/,"");
}


function setup_calendar( name ) {
    Calendar.setup({
        inputField     :    name,     // id of the input field
        ifFormat       :    "%d %b %Y",     // format of the input field (even if hidden, this format will be honored)
        showsTime      :    false,            // will display a time selector
        button         :    name + "_trigger",   // trigger for the calendar (button ID)
        singleClick    :    true,           // double-click mode
        step           :    1                // show all years in drop-down boxes (instead of every other year as default)
    });
}


function isNumber(number) {
    if ((number >= 0)||(number < 0)) return true;
    else return false;
}


function setCookie(c_name,value,expiredays)
{
    var exdate=new Date();
    exdate.setDate(exdate.getDate()+expiredays);
    document.cookie=c_name+ "=" +escape(value)+
    ((expiredays==null) ? "" : ";expires="+exdate.toGMTString());
}

function deleteCookie(name) {
  document.cookie = name +
  '=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
} 


function formatShortDate( date ) {
SMN = new Array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");

    var y = date.getFullYear();
    var m = date.getMonth();     // integer, 0..11
    var d = date.getDate();      // integer, 1..31
    
    return d + ' ' + SMN[m] + ' ' + y;
}

var description_id = false;
var block_description_id = false;

function showDescription( id ) {
    if ( description_id == id ) {
        $( description_id ).style.display = "none";
        description_id = '';
    } else {
        if ( description_id != false ) {
            $( description_id ).style.display = "none";
        }
        if ( block_description_id != false ) {
            $( block_description_id ).removeClassName( 'selected' );
            
        }
        
        description_id = id;
        $( id ).style.display = "block";
        $( 'b' + id ).addClassName( 'selected' );
        block_description_id = 'b' + id;
    }
}

function fluid_round( number, decimal_places ) {
    var factor = Math.pow(10,decimal_places);
    var result = Math.round(number*factor)/factor;
    return result;
}

function fluid_format( number, decimal_places ) {
    return new Number( number ).toFixed(decimal_places);
}

function fluid_format_currency(num, with_sign, zero_is_not_empty ) {
    num = num.toString().replace(/\$|\,/g, '');
    if (isNaN(num))
        num = "0";

    sign = (num == (num = Math.abs(num)));
    num = Math.floor(num * 100 + 0.50000000001);
    cents = num % 100;
    num = Math.floor(num / 100).toString();
    if (cents < 10)
        cents = "0" + cents;
        for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
            num = num.substring(0, num.length - (4 * i + 3)) + ',' +
                num.substring(num.length - (4 * i + 3));

//    return (((sign) ? '' : '-') + num + '.' + cents);

    value = (((sign) ? '' : '-') + num);
    
    if ( !zero_is_not_empty && value == 0 ) {
        value = '';
    } else {
        if ( with_sign ) {
            value = '$' + value;
        }
    }
    
    return value;
}


function formIsDirty(frm) {
    for (var i=0;i<frm.elements.length;i++) {
        var element = frm.elements[i];
        var type = element.type;
        if (type=="checkbox" || type=="radio") {
            if (element.checked != element.defaultChecked) return true;
        } else if (type=="hidden" || type=="password" || type=="text" || type=="textarea") {
            if (element.value != element.defaultValue) return true;
        } else if (type=="select-one" || type=="select-multiple") {
            for (var j=0;j<element.options.length;j++) {
                if (element.options[j].selected != element.options[j].defaultSelected) return true;
            }
        }
    }
    return false;
}

function run_table_filter( event ) {
	value = $('filter').value.toLowerCase();
	$$(".filter-tr").each(
		function (elmt ) {
			show=false;
			elmt.select( '.filter-entry' ).each (
				function ( filter_entry_element ) {
					if ( filter_entry_element.innerHTML.toLowerCase().indexOf( value ) == -1 ) {
//						elmt.hide();
					} else {
						show=true;
//						elmt.show();
					}
				}
			);
			if ( show ) {
				elmt.show();
			} else {
				elmt.hide();
			}
					
		}
	);
}


function align_table() {
	data_list = $$( '.resizedata' );
	total_width = 0;

	//field-date + field-rainfall + field-cover + field-growth + field-residual
	var initial_width = data_list.length * 200;
	$( 'resize-inner' ).setStyle( { width: ( initial_width ) + 'px' } );


	list = new Array();
	i=0;
	data_list.each ( function (data) {

		data.style.width = '';

		if ( data.hasClassName( 'hidden' ) ) {
			after_width = null;
		} else if ( data.hasClassName( 'break' ) ) {
			after_width = 4;
			total_width += ( 4 + 1 );//+1 for border between
		} else {
			after_width = ( data.getWidth() - 12 );
			total_width += ( data.getWidth() + 1 );//+1 for border between
		}


		if ( after_width != null ) {
			data.style.width = after_width + 'px';
		}


		list[i++] = after_width;
    } );


	total_width += 50;//Cater for the scrollbar
	$( 'resize-inner' ).setStyle( { width: ( total_width ) + 'px' } );


	i=0;
	$$( '.resizehead' ).each (
		function ( elmt ) {
			if ( list[i] != null ) {
				elmt.style.width = list[i++] + 'px';
			} else {
				i++;
			}
		}
	);
	
}




//**************************** TEST ****************
function test_numeric_data( id, message, required, min, max ) {
    if ( $( id ).value == '' ) {
        if ( required == true ) {
            return message;
        } else {
            $( id ).style.color = '';
            return '';
        }
    }

    if(isNaN( $( id ).value )) {
        $( id ).style.color = 'red';
        return message;
    } else {
        if ( !isNaN( min ) &&
              $( id ).value < min ) {
            $( id ).style.color = 'red';
            return message;
        }

        if ( !isNaN( max ) &&
              $( id ).value > max ) {
            $( id ).style.color = 'red';
            return message;
        }

        $( id ).style.color = '';
        return '';
    }
}

function test_money_data( id, message, required ) {
    if ( $( id ).value == '' ) {
        if ( required == true ) {
            return message;
        } else {
            $( id ).style.color = '';
            return '';
        }
    }

    value = $( id ).value;
    value = value.replace( ',', '' );
    
    if(isNaN( value )) {
        $( id ).style.color = 'red';
        return message;
    } else {
        $( id ).style.color = '';
        return '';
    }
}

function test_date_data( id, message, required ) {
    if ( $( id ).value == '' ) {
        if ( required == true ) {
            return message;
        } else {
            $( id ).style.color = '';
            return '';
        }
    }

    d = Date.parse( $( id ).value );
    if(isNaN(d)) {
        $( id ).style.color = 'red';
        return message;
    } else {
        $( id ).style.color = '';
        return '';
    }
}

function test_notempty_data( id, message ) {
    if ( $( id ).value == '' ) {
        $( id ).style.color = '';
        return message;
    } else {
        $( id ).style.color = '';
        return '';
    }
}


//**************************** Inline Popup ****************
var inlinePopupUrl = "";


function switchInlinePopup( url ) {
	new Ajax.Updater( 'inlinePopup', url, {
		evalScripts: true
	} );

	return false;
}


function showInlinePopup( url, element ) {
	inlinePopupUrl = url;
	if ( $( element ) != null ) {
		$( element ).style.visibility = 'hidden';
	}

	new Ajax.Updater( 'inlinePopup', url, {
		evalScripts: true,
		onComplete: function(transport){
			l_left = $( 'container' ).parentNode.getWidth() / 2 - $('inlinePopup').getWidth() / 2;
			$('inlinePopup').style.left = l_left + 'px';

			new Effect.Appear('inlinePopup', { from: 0.5, to: 1.0, duration: 0.5 });
			$( 'inlinePopup' ).show();
			new Draggable('inlinePopup', { handle: 'inlinePopupTitle' } );
		}
	} );
	new Effect.Fade('container', { from: 1.0, to: 0.5, duration: 0.5 });

	Event.observe( document, 'keyup', function(event){ if(event.keyCode == Event.KEY_ESC) { hideInlinePopup(); }});

	return false;
}


function hideInlinePopup( element )  {
	if ( $( element ) != null ) {
		$( element ).style.visibility = 'visible';
	}

	$( 'inlinePopup' ).hide();
	new Effect.Appear('container', { from: 0.5, to: 1.0, duration: 0.5 });
  
	return false;
}



//**************************** AJAX ****************
function fluid_ajax_submit( url, _parameters ) {
	new Ajax.Request( url, {
		parameters: _parameters,
		onComplete: function(transport) {

			if ( transport.status == 0 || transport.responseText.indexOf( 'Fatal error' ) > -1 ) {
				alert( "An unexpected error has occured processing your request.\nPlease try again later. " + transport.responseText );
				return false;
			} else if (transport.getResponseHeader('x-valid') == 'false') { 
				alert( transport.responseText ); 
				return false;
			} else {
				window.location = transport.responseText;
			}
		}
	});

	return false;
}


function fluid_ajax_delete( url, _parameters ) {
	if ( confirm( "Confirm delete ?" ) ) {
		fluid_ajax_submit( url, _parameters );
	} else {
		return false;
	}
}


function fluid_ajax_update( url, _parameters ) {
	fluid_ajax_submit( url, _parameters );
}


function fluid_set_response( transport ) {
//	$( 'info' ).innerHTML = $( 'info' ).innerHTML + "Standard function: " + fluid_set_response + "<br>";

}


function fluid_get_response( transport ) {
	
}


function fluid_error( transport ) {
	if ( transport.status == 0 || transport.responseText.indexOf( 'Fatal error' ) > -1 ) {
		alert( "An unexpected error has occured processing your request.\nPlease try again later. " + transport.responseText );
		return true;
	} else if (transport.getResponseHeader('x-valid') == 'false') { 
		alert( transport.responseText ); 
		return true;
	}


	return false;
}


function fluid_ajax( url, _parameters, method_string, success_function, error_function ) {

	new Ajax.Request( url, {
		method: method_string,
		parameters: _parameters,
		onComplete: function(transport) {

//			$( 'info' ).innerHTML = $( 'info' ).innerHTML + "fluid_ajax. 0: " + success_function + "<br>";
			if ( fluid_error( transport ) ) {
//				$( 'info' ).innerHTML = $( 'info' ).innerHTML + "fluid_ajax. 1: " + success_function + "<br>";
				error_function( transport );
			} else {
//				$( 'info' ).innerHTML = $( 'info' ).innerHTML + "fluid_ajax. 2: " + success_function + "<br>";
				success_function( transport );
//				$( 'info' ).innerHTML = $( 'info' ).innerHTML + "fluid_ajax. 3: " + success_function + "<br>";
			}
		}
	});


	return false;
}


function fluid_load_xml( payload ) {
	var xmlDoc;
	if (window.DOMParser) {
		parser=new DOMParser();
		xmlDoc=parser.parseFromString(payload,"text/xml");
	}
		else // Internet Explorer
	{
		xmlDoc=new ActiveXObject("Microsoft.XMLDOM");
		xmlDoc.async="false";
		xmlDoc.loadXML(payload);
	}


	return xmlDoc;
}


function fluid_set( payload, success_function, error_function ) {
	success_function = typeof(success_function) != 'undefined' ? success_function : fluid_set_response;
	error_function = typeof(error_function) != 'undefined' ? error_function : fluid_error;


	var url = "ajax/fluid.php";
	var _parameters = "payload=" + payload;

	fluid_ajax( url, _parameters, 'POST', success_function, error_function );


	return false;
}


function fluid_get( payload, success_function, error_function ) {
	success_function = typeof(success_function) != 'undefined' ? success_function : fluid_get_response;
	error_function = typeof(error_function) != 'undefined' ? error_function : fluid_error;


	var url = "ajax/fluid.php";
	var _parameters = "payload=" + payload;


	fluid_ajax( url, _parameters, 'GET', success_function, error_function );

	return false;
}


function fluid_set_json( name, parameters, success_function, error_function ) {
	success_function = typeof(success_function) != 'undefined' ? success_function : fluid_set_response;
	error_function = typeof(error_function) != 'undefined' ? error_function : fluid_error;


	var url = "ajax/fluid/" + name;


	fluid_ajax( url, parameters, 'POST', success_function, error_function );


	return false;
}


function fluid_get_json( name, parameters, success_function, error_function ) {
	success_function = typeof(success_function) != 'undefined' ? success_function : fluid_get_response;
	error_function = typeof(error_function) != 'undefined' ? error_function : fluid_error;


	var url = "ajax/fluid/" + name;


	fluid_ajax( url, parameters, 'GET', success_function, error_function );

	return false;
}

