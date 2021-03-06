$(document).ready(function(){
	

});


// bootstrap model
function doModal(heading, formContent) {
    html =  '<div id="form-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="confirm-modal" aria-hidden="true">';
    html += '<div class="modal-dialog">';
    html += '<div class="modal-content">';
    html += '<div class="modal-header">';
    html += '<a class="close" data-dismiss="modal">×</a>';
    html += '<h4>'+heading+'</h4>'
    html += '</div>';
    html += '<div class="modal-body">';
    html += formContent;
    html += '</div>';
    html += '<div class="modal-footer">';
    html += '<span class="btn btn-primary" data-dismiss="modal">Close</span>';
    html += '</div>';  // content
    html += '</div>';  // dialog
    html += '</div>';  // footer
    html += '</div>';  // modalWindow
    $('body').append(html);
    $("#form-modal").modal();
    $("#form-modal").modal('show');
	formsubmit();

    $('#form-modal').on('hidden.bs.modal', function (e) {
        $(this).remove();
        location.reload();
    });

}

function formsubmit()
{
	$("#formoid").submit(function(event) {

      /* stop form from submitting normally */
      event.preventDefault();

      /* get some values from elements on the page: */
      var $form = $( this ),
        url = $form.attr( 'action' );
		var data =  {
						"BaseName": $('#BaseName').val(), "DeviceType": $('#DeviceType').val(),
						"AntennaInt": $('#AntennaInt').val(), "EventType": $('#EventType').val(),
						"DeviceID": $('#DeviceID').val(), "PendantRxLevel": $('#PendantRxLevel').val(),
						"LowBattery": $('#LowBattery').val(), "TimeStamp": $('#TimeStamp').val(),
					};

		//var tmp = JSON.stringify($('.dd').nestable('serialize'));
		  // tmp value: [{"id":21,"children":[{"id":196},{"id":195},{"id":49},{"id":194}]},{"id":29,"children":[{"id":184},{"id":152}]},...]
		  $.ajax({
			type: 'POST',
			datatype:'json',
			url: url,
			data: JSON.stringify(data),
			success: function(msg) {
				//console.log(data);
			   alert(msg);
			   $('#form-modal').remove();
			   location.reload();
			},
			error:function(msg){
				alert('ERROR');
			}
		  });

    });

}

