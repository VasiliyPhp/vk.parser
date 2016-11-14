	$('#get-groups').click(function(ev){
		ev.preventDefault();
		ev.stopPropagation();
	  var formData = $('#find-groups-form').serialize();
		$.ajax({
			type : 'POST',
			dataType : 'json',
			data : formData,
			beforeSend : function(){
				$('#get-groups').attr('disabled', true);
			},
			complete : function(){
				$('#get-groups').attr('disabled', false);
			},
			success : function(resp){
				var resCont = $('#find-group-result'),
						i = 0,
						len,
						item,
						id_list = '';
				if(typeof resp.error != 'undefined'){
					resCont.html('<h4 id="result-error" > ' + resp.error.message + '</h4>');
					return ;
				}
				if(typeof resp.response.error != 'undefined'){
					resCont.html('<h4 id="result-error" > ' + resp.response.error.message + '</h4>');
					return ;
				}
				resCont.show();
				len = resp.response.length
				$('#result-error').remove();
				for(; i < len; i++){
					item = resp.response[i];
					id_list += item.id + "\n";
					resCont.append('<div style="height:310px" class="col-md-2 thumbnail" >'+
					  '<button onclick="$(this).parent().remove()" class="btn btn-block text-center remove-button btn-sm" ><span class="glyphicon glyphicon-trash"></span></button><a onclick="window.open(this.href);return false;" href="http://vk.com/'+item.screen_name+
						'" ><img class="media-object" src="'+item.photo_100+
						'" ></a><div class="caption text-center"><label>'+item.name+
						'<br>' + item.members_count + ' участников<br>'+(item.country?item.country.title + ', ':'')+
						(item.city?item.city.title:'')+'<br><input class="check-list" value="'+item.id+
						'" data-contacts="' + getContactList(item) + 
						'" data-count="' + item.members_count + '" type="checkbox"></label></div></div>')
				}
				
				addButtons();
			}
		})
	})
	
	$('#get-peoples').click(function(){
		var peoples = [],
		    checked = $('#find-group-result [type=checkbox]:checked'),
        len = checked.length,
				list = '';
		while(len--){
			peoples.push({
				id : checked[len].value,
				count : $(checked[len]).attr('data-count')
			});
			
		}
		
		$('#getpeopleform-peoples').val(JSON.stringify(peoples));
		$('#get-people-form').submit();
		
	})
	
	$('#get-contacts').click(function(){
		var checked = $('#find-group-result [type=checkbox]:checked'),
				len = checked.length,
				list = '',
				contacts,
				item;
		while(len--){
			item = checked[len];
			contacts = $(item).attr('data-contacts');
			if(contacts){
				list = list? list + ',' + contacts : contacts;
			}
		}
		$('#contact_ids').val(list);
		console.log($('#get-contact-ids-form'))
		$('#get-contact-ids-form').submit();
	})
	
	function getContactList(item){
		if(typeof item.contacts  == 'undefined'){
			return '';
		}
		var list = [],
		    contact,
				len = item.contacts.length;
		while(len--){
			contact = item.contacts[len];
			if(typeof contact.user_id == 'undefined'){
				continue;
			}
			list.push(contact.user_id);
		}
		return list.join(',');
	}
	
	function addButtons(){
		var cont = $('#find-group-result'),
				clearButtonId = 'clear-button';

		if($('#'+clearButtonId).length){
			return ;
		}
		
		$('<div class="btn btn-sm btn-danger" id='+clearButtonId+'>')
		  .click(function(){cont.hide('slow').html('')})
			.css({
				position:'fixed',
				bottom: 20,
				right: 20,
			})
			.appendTo(cont)
			.text('Очистить результаты');
		
		
		
	}











