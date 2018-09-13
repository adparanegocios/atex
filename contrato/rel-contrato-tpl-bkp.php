<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		
        {HEAD}
        
        <script>
        
        $(document).ready(function(){
		        	
			$("#Buscar").click(function(){
				
				/*var departamento = $("#departamento").val(); 
				
				if(departamento == ""){
					alert("Selecione o departamento!");
					return false;
					}*/
							
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
                
               <span style="color: #999; font-size: 15px">Contrato</span>
              
               <fieldset style="border: solid 1px #CCC; text-align: left;width: 1210px; margin-top: 30px">
               
               <legend style="margin-left: 10px">FILTRO</legend>
               <form method="post"  action="">
               <table cellpadding="3" cellspacing="3" style="margin: 10px; font-size: 13px">
               <tr>
               
               
               <td width="70">Departamento:</td>
               <td width="300">
               <!-- BEGIN BLOCK_CONTROLADORIA -->
				<select name="departamento" id="departamento">
					<option value="">Selecione um departamento</option>
					<option value="1" {SELECIONA_TEM}>TEM! Classificados</option>
					<option value="2" {SELECIONA_NOTICIARIO}>Comercial Notici&aacute;rio</option>
					<option value="3" {SELECIONA_WEB}>Comercial Web</option>
				</select>
				<!-- END BLOCK_CONTROLADORIA -->
               </td>
               
               
               <td width="65">Vigência:</td>
               <td width="240" >
               <input name="data1" value="{DATA_INICIO}" id="data1" style="text-align: center" size="10" /> - <input id="data2" style="text-align: center" value="{DATA_FIM}" name="data2" size="10" />
               </td>
               
               <td >
               <input type="submit" id="Buscar" value="Buscar" style="padding: 2px"/>
               </td>
               
               </tr>
               <tr ><td colspan="7" style="color:#666"><br><b>*</b> Obs: se n&atilde;o for especificado um per&iacute;odo, a data considerada sempre ser&aacute; a atual<br>
               <b>*</b>Obs: outras consultas, acessar m&oacute;dulo Contract Manager.
               </td></tr>
               <tr>
               <td colspan="7" style="color:#666">
               Var1: métrica principal de monitoramento de um contrato. Exemplo: valor bruto (R$).<br>
			   Var2: métrica secundária de monitoramento de um contrato (opcional). Exemplo: número de linhas.
               </td>
               </tr>
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
                                    <th style="width:20%">Contrato</th>
                                    <th style="width:20%">Cliente</th>
                                    <th style="width:21%">CPF/CNPJ</th>
                                    <th style="width:21%">In&iacute;cio</th> 
                                    <th style="width:21%">Fim</th>  
                                    <th style="width:21%">Inser&ccedil;&otilde;es</th>  
                                    <th style="width:21%">Percentagem Var1</th>  
                                    <th style="width:21%">Var1 Real</th>  
                                    <th style="width:21%">Var1 Esperado</th>
                                    <th style="width:21%">Resta Var1 </th>
                                    <th style="width:21%">OBS</th>
                                    <th style="width:21%">Pacote</th>
                                                                        
                                </tr>
                            </thead>
                            <tbody>
                                <!-- BEGIN BLOCK_DADOS -->
                                <tr >
                                <td {STYLE} class="align-center">{CONTRATO}</td>
								    <td {STYLE} class="align-center">{CLIENTE}</td>
								    <td {STYLE} class="align-center">{DOCUMENTO}</td>
								    <td {STYLE} class="align-center">{DATAI}</td>
								    <td {STYLE} class="align-center">{DATAF}</td>
                                    <td {STYLE} class="align-center">{QTDPUBLICACOES}</td>
                                    <td {STYLE} class="align-center">{PERCVAR1}</td>
                                    <td {STYLE} class="align-center">{VAR1REAL}</td>
                                    <td {STYLE} class="align-center">{VAR1ESPERADO}</td>
                                    <td {STYLE} class="align-center">{RESTAVAR1}</td>
                                    <td {STYLE} class="align-center">{OBS}</td>
                                    <td {STYLE} class="align-center">{PACOTE}</td>
                                </tr>
                                <!-- END BLOCK_DADOS -->
                                                                 
                                
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
                                    <option value="10" >10</option>
                                    <option value="20">20</option>
                                    <option value="30" >30</option>
                                    <option value="40" selected="selected">40</option>
                                </select>
                                </div>
                            </form>
                        </div>
                        
                        <div style="clear: both"></div>
                     </div> <!-- End .module-table-body -->
                     
                </div> <!-- End .module -->
                 
                 
                 <!-- END BLOCK_TABELA -->
                 
                 <!--  BEGIN BLOCK_VAZIO -->
                 <!--  END BLOCK_VAZIO -->
                 
			</div> <!-- End .grid_12 -->
                
            <!-- Categories list -->
            <div class="grid_6">
                
                <div style="clear:both;"></div>
			</div> <!-- End .grid_6 -->
            
        </div> <!-- End .container_12 -->
		
	</body>
</html>