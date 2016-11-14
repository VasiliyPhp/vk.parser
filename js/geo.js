
// отправка координат 
	$('#recognize_coords').click(function(ev){
		var $coords = $('#coords-coords').val();
		if(!$coords){
			return ;
		};
		$.ajax({
			url : 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' + $coords,
			dataType : 'jsonp',
			beforeSend: function(){
				$('#geo-result')
				  .css("background", "rgba(0,0,0,.3)");
			},
			success : function(resp){
 				// alert(typeof resp.error)
				// alert(JSON.stringify(resp, false, ' '))
 			  $('#geo-result')
				  .css("background", "transparent");
			
				if(typeof resp.error == 'object'){
					$('#coords_address').html('<h4 style="color:red">' + resp.error.message + '</h4>')
					return ;
				}
				var Geo = resp.response.GeoObjectCollection.featureMember[0].GeoObject,
				    point = Geo.Point.pos,
				    address = Geo.description + ', ' + Geo.name;
				$('#coords_address').text(address);
				$('#coords_point').val(point);
			}
		})
		ev.preventDefault();
		ev.stopPropagation();
		return false
	})
	
	$('.site-index').on('click', '.find_photos', find_photos);
	$('.site-index').on('click', '.caption', show_details_location);
	
	function show_details_location(ev){
		ev.stopPropagation();
		ev.preventDefault();
		var $coords = $(this).find('.coords').text();
		    $this = $(this);
		$.ajax({
			url : 'https://geocode-maps.yandex.ru/1.x/?format=json&geocode=' + $coords,
			dataType : 'jsonp',
			beforeSend: function(){
				$this.css('background', 'rgba(0,0,0,.5)');
			},
			complete: function(){
				$this.css('background', 'tranparent');
			},
			success: function(resp){
				// alert(JSON.stringify(resp, false, ' '));
				if(typeof resp.error == 'object'){
					alert('ошибка - ' + resp.error.message)
					return ;
				}
				var Geo = resp.response.GeoObjectCollection.featureMember[0].GeoObject,
				    address = Geo.description + ', ' + Geo.name;
				alert(address);
			},
		})
	}
	
	function find_photos(){
		$('#found_photos').html('');
		var page;
		switch($(this).attr('data-page')){
		case 'first':
		  page = 0;
			break;
		case 'next':
		  page = $('#found_photos').data('page') ? $('#found_photos').data('page') : 0;
			break;
		case 'prev':
		  // console.log($('#found_photos').data('page'))
		  page = $('#found_photos').data('page') ? $('#found_photos').data('page') - 2 : 0;
			// console.log(page);
			break;
		}
		var data = {
			Coords:{
			  radius: $('#photos_radius').val(),
		    point : $('#coords_point').val(),
				page : page,
			},
		};
		$('#found_photos').data('page', ++page);
	  $.post(null, data, place_found_photos);
	}
	
	function place_found_photos(resp){
		// $('#found_photos').append('<pre>' + JSON.stringify(resp.query, null, '\t') + '</pre>');
		if(typeof resp.error !== 'undefined' ){
  		$('#found_photos').append('<h2 style="color:red">'+resp.error.error_msg+'</h2>');
			return ;
		}
		var items = resp.response.items;
		if(!items.length){
				$('#found_photos').append('<h2 style="color:#556">Не найдено</h2>');
			return ;
		}
		
		$('#found_photos').append('<h2>Результаты: ( ' + items.length + ' )</h2>');
		$('#found_photos').append('<div class="well">Страница ' + $('#found_photos').data('page') + '</div>');
		($('#found_photos').data('page') - 1) && $('<div class="form-group"><button data-page="prev" class="btn-sm btn btn-primary find_photos" >назад</button></div>').appendTo('#found_photos')
		$('<div class="form-group"><button data-page="next" class="btn-sm btn btn-primary find_photos" >далее</button></div>').appendTo('#found_photos')
		$('<div id="col-cont" class="column-container">').appendTo('#found_photos')
		var count = items.length,
				content,
				coords,
				href,
				album_id,
				i = 0,
				date;
		while( count > i ){
			date = new Date(items[i].date * 1000);
			date = date.toLocaleString();
			coords = items[i].long + ' ' + items[i].lat;
			// href = items[i].owner_id < 0 ? 
			  // 'http://vk.com/club' + Math.abs(items[i].owner_id) :
			  // 'http://vk.com/id' + items[i].owner_id;
		  switch(items[i].album_id){
			case -6 :
			  album_id = '0'; break;
			case -7 :
			  album_id = '00'; break;
			case -15 :
			  album_id = '000'; break;
			default :
			  album_id = items[i].album_id;
			}
			href = 'http://vk.com/album' + items[i].owner_id + '_' + album_id;
  		content = '<div class="column-content"><div class="thumbnail">' + 
			  '<a title="перейти на страницу пользователя" onclick="window.open(this.href); return false;" href="' + href + '" target="_blank">' +
			  '<img src="' + findPhoto(items[i]) + '">'+
				'</a><div class="caption" style="cursor:pointer" title="узнать точный адрес фотографии"><div class="text-center"><mark class="coords">' + coords + '</mark></div><div class="text-right"><ins>' + date + 
				'</ins></div><p>' + items[i].text + '</p></div></div></div>';

			$(content).appendTo('#col-cont');
			i++;
		}
		($('#found_photos').data('page') - 1) && $('<div class="clearfix"></div><div class="form-group"><button data-page="prev" class="btn-sm btn btn-primary find_photos" >назад</button></div>').appendTo('#found_photos')
		$('<div class="form-group"><button data-page="next" class="btn-sm btn btn-primary find_photos" >далее</button></div>').appendTo('#found_photos')
	}
	
	
	 function findPhoto($array){
		return $array.photo_604 || $array.photo_130 || $array.photo_75;
	}
	
	
	