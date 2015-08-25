/**
 * Functionality for the single ticket page in the admin
 **/
jQuery( document ).ready( function(){
	if( ! jQuery( '.ticket-single').length )
		return;
	
	/**
	 * Toggle functionality for answers
	 **/
	jQuery( '#sts-wrap' ).on( 'click', '.history-item h3', function(){
		jQuery( this ).parent().find( '.entry' ).slideToggle();
	});

	/**
	 * Open an answer on load, when the #answer-ID is
	 * in the URL. This link is provided in the answer email
	 **/
	var url = window.location.hash;
	if( url.match( /#answer-(.*)/ ) )
		jQuery( window.location.hash ).find( 'h3' ).click();
	

});

/**
 * Functionality for the settings page in the admin
 **/
jQuery( document ).ready( function(){
	if( jQuery( '.ticket-single').length )
		return;

	//Clear the "New form field" dialog
	jQuery( '#btn-sts-create-new-ticket-field' ).click( function(){
		jQuery( '#sts-create-new-ticket-field' ).find('input,select,textarea' ).each( function(){
			jQuery( this ).val( '' );
			if( jQuery( this ).attr( 'id' ) == 'tag' )
				jQuery( this ).val( 'input' );
			jQuery( '#sts-formfield-choices-wrapper' ).hide();
		});
	});

	//Autogenerate the metakey input field
	jQuery( '#label' ).keyup( function(){
		var metakey = jQuery( this ).val();
		if( metakey.trim() == '' )
			return;

		metakey = metakey.toLowerCase();
		metakey = metakey.replace( / /ig, '-' );
		metakey = metakey.replace( /[^a-z0-9_\-]/ig, '' );
		jQuery( '#metakey' ).val( metakey );
	});
		jQuery( '#label' ).change( function(){
			jQuery( this ).keyup();
		})

	//Check, whether the choices textbox needs to be displayed
	jQuery( '#tag').change( function(){
		if( jQuery( this ).val() == 'select' )
			jQuery( '#sts-formfield-choices-wrapper' ).slideDown();
		else
			jQuery( '#sts-formfield-choices-wrapper' ).slideUp();
	});

	//Make the meta field list sortable
	jQuery( '.ticket-field-list' ).sortable();

	//Button action
	//Create a new meta field
	jQuery( '#do-sts-create-new-ticket-field' ).click( function( event ){
		event.preventDefault();
		var li = '<li class="editable">';

		var data = {}
		jQuery( this ).closest( '#TB_ajaxContent' ).find('input,select,textarea' ).each( function(){
			data[ jQuery( this ).attr( 'id' ) ] = jQuery( this ).val();
		});
		data.choices = data.choices.split("\n");

		var metakey_index = 1;
		var metakey_tmp = data.metakey;
		while( jQuery( 'input[name="ticket[fields][id][]"][value="' + metakey_tmp + '"]').length ){
			metakey_index++;
			metakey_tmp = data.metakey + '-' + metakey_index;
		}
		if( metakey_index > 1 )
			data.metakey = metakey_tmp;

		var json = JSON.stringify( data );
		li += '<input name="ticket[fields][id][]" value="' + data.metakey + '" type="hidden"/><textarea name="ticket[fields][fields][]" style="display:none;">' + json + '</textarea>' + data.label;
		li += '<div title="' + stsLocalize.trash + '" class="sts-delete dashicons dashicons-trash"></div>';
		li += '<div title="' + stsLocalize.edit + '" class="sts-edit-field dashicons dashicons-edit"></div>';
		li += '</li>';
		jQuery( li ).appendTo( '.ticket-field-list' );
		jQuery( '.ticket-field-list' ).sortable();
		tb_remove();
	});

	//Fill the Edit Field Form and display it
	jQuery( '.ticket-field-list' ).on( 'click', '.sts-edit-field', function(){
		var li = jQuery( this ).parent();
		var index = jQuery( '.ticket-field-list li' ).index( li );

		var json = JSON.parse( jQuery( this ).parent().find( 'textarea' ).val() );
		jQuery( '#edit_li_index').val( index );
		jQuery( '#edit_metakey').val( json.metakey );
		jQuery( '#edit_metakey_display').text( json.metakey );
		jQuery( '#edit_tag').val( json.tag );
		var tag = stsLocalize.inputfield;
		if( json.tag == 'select' ){
			tag = stsLocalize.selectbox;
			jQuery( '#sts-formfield-choices-edit-wrapper' ).show();
		} else {
			jQuery( '#sts-formfield-choices-edit-wrapper' ).show();
		}
		jQuery( '#edit_tag_display').text( tag );

		var text = '';
		jQuery( '#sts-formfield-choices-edit-wrapper' ).hide();
		if( json.tag != 'input' && typeof( json.choices ) == 'object' ){
			jQuery( '#sts-formfield-choices-edit-wrapper' ).show();
			for(var k in json.choices ){
				if( text != '' )
					text += '\n';
				text += json.choices[k];
			}
		}
		jQuery( '#edit_choices' ).val( text );
		jQuery( '#edit_label').val( json.label );
		jQuery( '#btn-sts-edit-ticket-field' ).click();
	});
	
	//Button action
	//Change the metafield
	jQuery( '#do-sts-edit-ticket-field' ).click( function( event ){
		event.preventDefault();
		var oldLi = jQuery( '.ticket-field-list' ).find( 'li' ).eq( parseInt( jQuery( '#edit_li_index' ).val() ) );
		console.log( oldLi );

		var li = '<li class="editable">';

		var data = {}
		jQuery( this ).closest( '#TB_ajaxContent' ).find('input,select,textarea' ).each( function(){
			var key = jQuery( this ).attr( 'id' ).replace( /edit_/, '' );
			if( key != 'li_index' )
				data[ key ] = jQuery( this ).val();
		});
		data.choices = data.choices.split("\n");
		var json = JSON.stringify( data );
		li += '<input name="ticket[fields][id][]" value="' + data.metakey + '" type="hidden"/><textarea name="ticket[fields][fields][]" style="display:none;">' + json + '</textarea>' + data.label;
		li += '<div title="' + stsLocalize.trash + '" class="sts-delete dashicons dashicons-trash"></div>';
		li += '<div title="' + stsLocalize.edit + '" class="sts-edit-field dashicons dashicons-edit"></div>';
		li += '</li>';
		jQuery( li ).insertAfter( oldLi );
		oldLi.remove();
		jQuery( '.ticket-field-list' ).sortable();
		tb_remove();

	});

	//Remove a metafield
	jQuery( '.ticket-field-list' ).on( 'click', '.sts-delete', function(){
		jQuery( this ).parent().remove();
	});

});

jQuery( document ).ready( function(){
	jQuery( '#sts-tabs' ).tabs();
});