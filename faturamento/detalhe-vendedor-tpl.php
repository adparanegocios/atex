<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		
        {HEAD}
        
		 <script type="text/javascript" src="../colorbox/jquery.colorbox.js"></script>
         <link rel="stylesheet" type="text/css" href="../colorbox/colorbox.css" media="screen" />
        
        <script>
        
        $(document).ready(function(){
			
			$('.detalhe').colorbox({width:400, height:540,iframe:true });
			
			$("#Buscar").click(function(){
				<!-- BEGIN BLOCK_CONTROLADORIA_VALIDACAO -->
				var acesso = $("#acesso").val();
	            if(acesso == ""){
					alert("Selecione o tipo de acesso!");
					return false;
	            }
	            <!-- END BLOCK_CONTROLADORIA_VALIDACAO -->
				var data_tipo = $("#fDataTipo").val(); 
				if(data_tipo == ""){
					alert("Selecione o tipo da Data!");
					return false;
					}
				$(".module").hide();
				$(this).fadeOut(200)
				$("#load").fadeIn();
			})
            
            
        });
        
        </script>
        </head>
	<body>
    	<!-- Header -->
         <!-- End #header -->
        
		
        
            <div style="clear:both;"></div>
                              
                <div class="module">
                	<h2><span>Detalhes</span></h2>
                  
                    <div class="module-table-body">
                    	<form action="">
                        <table id="myTable" class="tablesorter">
                        	<thead>
                                <tr>
                                    <th style="width:15%">Número da Ordem</th>
                                    <th style="width:25%">Cliente</th>
                                    <th style="width:10%">Valor</th>
                                    <!-- BEGIN BLOCK_TIPO_ANUNCIO_COLUNA -->
                                    <th style="width:21%">Caderno</th>
                                    <!-- END BLOCK_TIPO_ANUNCIO_COLUNA -->
                                    <th style="width:20%">Primeira Data</th>
                                    <th style="width:30%">Última Data</th>
                                </tr>
                            </thead>
                            <tbody>
                               <!-- BEGIN BLOCK_DETALHE -->
                                <tr>
                                    <td class="align-center">{ORDEM}</td>
                                    <td class="align-center">{NOME}</td>
                                    <td>{VALOR}</td>
                                    <!-- BEGIN BLOCK_TIPO_ANUNCIO -->
                                    <td>{CADERNO}</td>
                                    <!-- END BLOCK_TIPO_ANUNCIO -->
                                    <td>{DT_INICIO}</td>
                                    <td>{DT_FIM}</td>
                                </tr>
                               <!-- END BLOCK_DETALHE -->
                            </tbody>
                        </table>
                        
                        
                     </div> <!-- End .module-table-body -->
                     
               
                 
                 
                 
                 
                 
			</div> <!-- End .grid_12 -->
                
            <!-- Categories list -->
            
            
        
		
	</body>
</html>