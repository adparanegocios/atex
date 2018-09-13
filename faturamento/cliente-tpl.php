<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		
        {HEAD}
        
		 <script type="text/javascript" src="../colorbox/jquery.colorbox.js"></script>
         <link rel="stylesheet" type="text/css" href="../colorbox/colorbox.css" media="screen" />
        
        <script>
        
        $(document).ready(function(){
			
			$('.detalhe').colorbox({width:'90%', height:440,iframe:true });
			
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
        <div id="header">
            <!-- Header. Status part -->
            
             <div id="header-status">
                {TOPO}
                
            </div> <!-- End #header-status -->
            
            <!-- Header. Main part -->
            <div id="header-main">
            
            {MENU}
            
            </div> <!-- End #header-main -->
            
            <div style="clear: both;"></div>
            <!-- Sub navigation -->
            <div id="subnav">
            
                <div class="container_12">
                
                    <div class="grid_12">
                    
                        <ul style="margin-top: 3px;">
                            {SUBMENU}                       
                        </ul>
                        
                    </div><!-- End. .grid_12-->
                    
                </div><!-- End. .container_12 -->
                
                <div style="clear: both;"></div>
                
            </div> <!-- End #subnav -->
            
        </div> <!-- End #header -->
        
		<div class="container_12">
        
            <div style="clear:both;"></div>
            
            
            
            <div class="grid_12">
                
               <span style="color: #999; font-size: 15px">Financeiro - <span style="color: #666">Cliente</span></span>
              
               <fieldset style="border: solid 1px #CCC; text-align: left;width: 1034px; margin-top: 30px">
               
               <legend style="margin-left: 10px">FILTRO</legend>
               <form method="post"  action="">
               <table cellpadding="3" cellspacing="3" style="margin: 10px; font-size: 13px">
               <tr>
               
               <!-- BEGIN BLOCK_CONTROLADORIA_SELECT -->
               <td width="70">Acesso:</td>
               <td width="130">
               <select name="fAcesso" id="acesso">
                <option value="">--Selecione--</option>
               <option value="noticiario" {NOTICIARIO_SELECIONA}>Noticiario</option>
               <option value="tem" {TEM_SELECIONA}>Tem</option>
               <option value="web" {WEB_SELECIONA}>Web</option>
               </select>
               </td>
               <!-- END BLOCK_CONTROLADORIA_SELECT -->
               
               <td width="80">Data de:</td>
               <td width="130">
               <select name="fDataTipo" id="fDataTipo" >
               <option value="">-Selecione-</option>
               <option value="publicacao" {PUBLICACAO_SELECIONA}>veiculacao</option>
               <option value="criacao" {CRIACAO_SELECIONA}>criacao</option>
               </select>
               </td>
               
               <td width="65">Periodo:</td>
               <td width="240" >
               <input name="fDataInicio" value="{DATA_INICIO}" id="data1" style="text-align: center" size="10" /> - <input id="data2" style="text-align: center" value="{DATA_FIM}" name="fDataFim" size="10" />
               </td>
               
               <td width="120">Nome do Cliente:</td>
               <td width="130">
               <input name="fNome" value="{NOME_CLIENTE}" id="nome_cliente" size="15" />
               </td>
               
               <td >
               <input type="submit" id="Buscar" value="Buscar" style="padding: 2px"/>
               </td>
               
               </tr>
               <tr ><td colspan="7" style="color:#666"><br><b>*</b> Obs: se n&atilde;o for especificado um per&iacute;odo, a data considerada sempre ser&aacute; a atual</td></tr>
               </table>
               </form>
               </fieldset>
               
              <br><br>
               
               <div style="display: none;margin-top: 70px" id="load"><center><img src='../img/ajax-loader.gif' ></center></div>
                
               <!-- BEGIN BLOCK_TABELA -->
                
               
                <div class="module">
                	<h2><span>Resultados</span></h2>
                  
                    <div class="module-table-body">
                    	<form action="">
                        <table id="myTable" class="tablesorter">
                        	<thead>
                                <tr>
                                    <th style="width:5%">Doc do cliente</th>
                                    <th style="width:20%">Cliente</th>
                                    <th style="width:21%">Valor</th>
                                    <th style="width:21%">Detalhe</th>
                                    
                                </tr>
                            </thead>
                            <tbody>
                                <!-- BEGIN BLOCK_LINHA -->
                                <tr>
                                    <td class="align-center">{DOC}</td>
                                    <td class="align-center">{CLIENTE}</td>
                                    <td>{TOTAL}</td>
                                    <td><a class="detalhe" href="detalhe.php?doc={DOC}&area={AREA}&dt_inicio={DATA_INICIO}&dt_fim={DATA_FIM}&tipo_data={DATA_DE}">Detalhe</a></td>
                                </tr>
                                <!-- END BLOCK_LINHA -->
                                <tr>
                                <td > </td>
                                <td > </td>
                                <td align="center"><b>Total:</b> R$ {TOTAL_GERAL}</td>
                                </tr>
                            </tbody>
                        </table>
                        </form>
                        <div class="pager" id="pager">
                            <form action="">
                                <div>
                                <img class="first" src="../img/arrow-stop-180.gif" tppabs="http://www.xooom.pl/work/magicadmin/images/arrow-stop-180.gif" alt="first"/>
                                <img class="prev" src="../img/arrow-180.gif" tppabs="http://www.xooom.pl/work/magicadmin/images/arrow-180.gif" alt="prev"/> 
                                <input type="text" class="pagedisplay input-short align-center"/>
                                <img class="next" src="../img/arrow.gif" tppabs="http://www.xooom.pl/work/magicadmin/images/arrow.gif" alt="next"/>
                                <img class="last" src="../img/arrow-stop.gif" tppabs="http://www.xooom.pl/work/magicadmin/images/arrow-stop.gif" alt="last"/> 
                                <select class="pagesize input-short align-center">
                                    <!-- BEGIN BLOCK_PAGE -->
									<option value="{PAGE}" {SELECIONADO}>{PAGE}</option>
									<!-- END BLOCK_PAGE -->
                                </select>
                                </div>
                            </form>
                        </div>
                        
                        <div style="clear: both"></div>
                     </div> <!-- End .module-table-body -->
                     
                </div> <!-- End .module -->
                 
                 
                 <!-- END BLOCK_TABELA -->
                 
                 
			</div> <!-- End .grid_12 -->
                
            <!-- Categories list -->
            <div class="grid_6">
                
                <div style="clear:both;"></div>
			</div> <!-- End .grid_6 -->
            
        </div> <!-- End .container_12 -->
		
	</body>
</html>