const operator = {
	broadcast_graphic : null,
	jenis_broadcast_graphic: null, //tanding || seni
	init : function($data_broadcast_graphic, $jenis_broadcast_graphic){
		operator.broadcast_graphic = $data_broadcast_graphic;
		operator.jenis_broadcast_graphic = $jenis_broadcast_graphic;
		operator.refresh_broadcast_graphic();	
	},
	update_status: function ($id_broadcast_graphic, el) {
		$updated_status = $(el).data('status');
		$.post("broadcast-graphic/update/" + $id_broadcast_graphic,
			{
				"status": $updated_status
			}
			,
			function (data, textStatus, jqXHR) {
				if (data.status == true) {
					$.each(operator.broadcast_graphic, function (i, broadcast_graphic) { 
						if(broadcast_graphic.id_broadcast_graphic == $id_broadcast_graphic){
							operator.broadcast_graphic[i].status = $updated_status;
						}
					});
					// operator.broadcast_graphic[$index_broadcast_graphic].status = $updated_status;
					operator.update_button_state();
				} else {
					alert('error');
				}
			},
			"json"
		);
	},
	update_autorefresh: function ($id_broadcast_graphic, el) {
		$updated_status = $(el).data('autorefresh');
		
		$.post("broadcast-graphic/update/" + $id_broadcast_graphic,
			{
				"autorefresh": $updated_status
			}
			,
			function (data, textStatus, jqXHR) {
				if (data.status == true) {
				
					$.each(operator.broadcast_graphic, function (i, broadcast_graphic) { 
						if(broadcast_graphic.id_broadcast_graphic == $id_broadcast_graphic){
							operator.broadcast_graphic[i].autorefresh = parseInt($updated_status);
						}
					});

					operator.update_button_state();
				} else {
					alert('error');
				}
			},
			"json"
		);
	},
	update_button_state : function(){
		$.each(operator.broadcast_graphic, function (i, v) { 
			
			 if(v.status == "active"){
				$('#broadcastGraphic'+v.id_broadcast_graphic).find('.btn').removeAttr('disabled');
				$('#broadcastGraphic'+v.id_broadcast_graphic).find('.active-button').removeClass('btn-secondary').addClass('btn-primary').data('status', 'deactive');
			 }else if(v.status == "deactive"){
				$('#broadcastGraphic'+v.id_broadcast_graphic).find('.btn').removeAttr('disabled');
				$('#broadcastGraphic'+v.id_broadcast_graphic).find('.active-button').removeClass('btn-primary').addClass('btn-secondary').data('status', 'active');
			 }else if (v.status == "refresh"){
				$('#broadcastGraphic'+v.id_broadcast_graphic).find('.refresh-button').attr('disabled', true);
			 }else if (v.status == "timed-3s"){
				$('#broadcastGraphic'+v.id_broadcast_graphic).find('.timed-3s-button').attr('disabled', true);
			 }else if (v.status == "timed-5s"){
				$('#broadcastGraphic'+v.id_broadcast_graphic).find('.timed-5s-button').attr('disabled', true);
			 }
			 
			 if(parseInt(v.autorefresh) == 1){
				$('#broadcastGraphic'+v.id_broadcast_graphic).find('.autorefresh-button').removeClass('btn-secondary').addClass('btn-dark').data('autorefresh', 0);
			 }else{
				$('#broadcastGraphic'+v.id_broadcast_graphic).find('.autorefresh-button').removeClass('btn-dark').addClass('btn-secondary').data('autorefresh', 1);
			 }
		});
	},
	refresh_broadcast_graphic : function () {
		$.getJSON("broadcast-operator/refresh-broadcast-graphic/"+operator.jenis_broadcast_graphic,
			function (data, textStatus, jqXHR) {
				operator.broadcast_graphic = data;
				operator.update_button_state();
			}
		).always(function () {
			setTimeout(() => {
				operator.refresh_broadcast_graphic();
			}, 3000);
		});
	}
}
