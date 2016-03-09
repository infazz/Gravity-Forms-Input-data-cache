(function($){
	$(document).ready(function(){


		function elementStartsWith(el, str) {
		  return $(el).map( function(i,e) {
		    var classes = e.className.split(' ');
		    for (var i=0, j=classes.length; i < j; i++) {
		      if (classes[i].substr(0, str.length) == str) return e;
		    }
		  }).get(0);
		}


		function classStartsWith(el, str) {
		  return $(el).map( function(i,e) {
		    var classes = e.className.split(' ');
		    for (var i=0, j=classes.length; i < j; i++) {
		      if (classes[i].substr(0, str.length) == str) return classes[i];
		    }
		  }).get(0);
		}


		jQuery(document).on('gform_post_render', function(){
			gform_post_render();
		});

		function gform_post_render(){
			console.log('input data'); 



			$('.gform_store_input_data').parents('.gform_wrapper').each(function(){
				var el = $(this);

				///*
				var dataSaveCheckbox = $(this).find('.gform_store_input_data');

				dataSaveCheckbox.on('change', function(e){
					if($(this).is(':checked')){
						Cookies.set('cache-savedata', 'true');
					}else{
						Cookies.set('cache-savedata', 'false');
						doDataClear( el );
					}
				});
				//*/


				console.log( dataSaveCheckbox.is(':checked') );

				if(Cookies.get('cache-savedata') == 'true'){
					dataSaveCheckbox.prop('checked', true);
					dataSaveCheckbox.trigger('change');
					doDataShow( el );
				}else{
					doDataClear( el );
				}


				$(this).find('.gform_button').on('click', function(){
					doDataSave( el );
				});
			});

			

			function preg_quote( str ) {
			    return (str+'').replace(/([\\\.\[\]\"\"])/g, "");
			}

			function doDataClear( el ){
				//console.log('clear');

				var id = el.attr('id');
				el.find('.gfield').each(function(){
					var key = id + '-' + classStartsWith( $(this), 'cache-' );
					Cookies.set(key, '');
				});
			}


			function doDataSave( el ){
				//console.log('save');

				var id = el.attr('id');

				el.find('.gfield').each(function(){

					var input = $(elementStartsWith( $(this), 'cache-' )).find('input, select, textarea');
					var container = input.parents('.ginput_container')
					var key = id + '-' + input.attr('id');
					var value = input.val();


					if(container.hasClass('ginput_container_name')){
						input.each(function(k){
							var input_id = $(this).attr('id');
							key = id + '-' + input_id;
							value = $(this).val();
							Cookies.set(key, value);
							//console.log( k, input, input_id, key, value );
						});
					}else if( input.attr('type') == 'radio' || input.attr('type') == 'checkbox' ){
						input.each(function(k){
							var input_id = $(this).attr('id');
							key = id + '-' + input_id;
							value = '';
							if($(this).is(':checked')){
								value = '=checked=';
							}
							Cookies.set(key, value);
							//console.log( k, input, input_id, key, value );
						});
					}else{
						if(value != undefined) {
							Cookies.set(key, value);
							console.log(key, value);
						}
					}
				});
			}



			function doDataShow( el ){
				//console.log('show');

				var id = el.attr('id');
				el.find('.gfield').each(function(){

					//var key = id + '-' + classStartsWith( $(this), 'cache-' );

					var input = $(elementStartsWith( $(this), 'cache-' )).find('input, select, textarea');
					var container = input.parents('.ginput_container')
					var key = id + '-' + input.attr('id');
					var value = Cookies.get(key);


					if(container.hasClass('ginput_container_name')){
						input.each(function(k){
							var input_id = $(this).attr('id');
							key = id + '-' + input_id
							var value = Cookies.get(key);

							if(value != ''){
								$(this).val(value);
							}
						});
					}else if( input.attr('type') == 'radio' || input.attr('type') == 'checkbox' ){
						input.each(function(k){
							var input_id = $(this).attr('id');
							key = id + '-' + input_id
							var value = Cookies.get(key);

							if(value == '=checked='){
								$(this).prop( 'checked', true ).trigger('change');
							}
						});
					}else{

						if(value != ''){
							//console.log(value);

							if(value != undefined && value.indexOf('["') != -1){
								value = preg_quote(value);
								value = value.split(',');

								//console.log( value );
								$(elementStartsWith( $(this), 'cache-' )).find('input, select, textarea').val( value ).trigger('change');

							}else{
								$(elementStartsWith( $(this), 'cache-' )).find('input, select, textarea').val( value ).trigger('change');
							}

							
						}
						//console.log( $(elementStartsWith( $(this), 'cache-' )).find('input, select, textarea').val() );
					}

				});
			}
		}

		gform_post_render();


	

	});
})(window.jQuery);