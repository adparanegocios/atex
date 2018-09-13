$(document).ready(function(){
	
	$('#data1').focus(function(){
		$(this).calendario({ 
			target:'#data1'
		});
	});
	
	$('#data2').focus(function(){
		$(this).calendario({ 
			target:'#data2'
		});
	});
	
	$('#pesquisar').click(function(){
		
		var dados = $('#rel-totvs').serialize();
		
		$.ajax({
			type: "POST",
			url: 'rel-totvs-tpl.php',
			data: dados,
			beforeSend: function (data) {
				$('#resultado').html('<center><br><br><br><br><br><img src="../img/ajax-loader.gif"><br><br><br><br><br></center>');
			},
			success: function(data){
				$('#resultado').html(data);
			}
		});
		
	});
	
});