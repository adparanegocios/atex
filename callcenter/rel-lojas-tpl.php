<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		
        {HEAD}
        <script>
        
        $(document).ready(function(){

        	
			$("#Buscar").click(function(){
				
				var data_tipo = $("#fDataTipo").val(); 
				var time = $("#time").val(); 
				
				if(data_tipo == ""){
					alert("Selecione o tipo da Data!");
					return false;
					}
				if(time == ""){
					alert("Selecione o acesso!");
					return false;
					}
				
				
				$(".module").hide();
				$(this).fadeOut(200)
				$("#load").fadeIn();
			})
			
			$("#myTable2") 
				.tablesorter({
					// zebra coloring
					widgets: ['zebra'],
					// pass the headers argument and assing a object 
					headers: { 
						// assign the sixth column (we start counting zero) 
						6: { 
							// disable it by setting the property sorter to false 
							sorter: true 
						} 
					}
				})
			$("#myTable4") 
				.tablesorter({
					// zebra coloring
					widgets: ['zebra'],
					// pass the headers argument and assing a object 
					headers: { 
						// assign the sixth column (we start counting zero) 
						6: { 
							// disable it by setting the property sorter to false 
							sorter: true 
						} 
					}
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
                
               <span style="color: #999; font-size: 15px">Caixa</span>
              
               <fieldset style="border: solid 1px #CCC; text-align: left;width: 1210px; margin-top: 30px">
               
               <legend style="margin-left: 10px">FILTRO</legend>
               <form method="post"  action="">
               <table cellpadding="3" cellspacing="3" style="margin: 10px; font-size: 13px">
               <tr>
               
               
               <td width="70">Acesso:</td>
               <td width="300">
               <input type="hidden" name="time" id="time" value="20" />{DESCRICAO}
                <!-- BEGIN BLOCK_NOTICIARIO -->
					
                <!-- END BLOCK_NOTICIARIO -->
                <!-- BEGIN BLOCK_TEM -->
                
                <!-- END BLOCK_TEM -->
                <!-- BEGIN BLOCK_OPTION_LOTACAO -->
                
                <!-- END BLOCK_OPTION_LOTACAO -->
               
               </td>
               
               <td width="90">Usuario:</td>
               <td width="250">
               <select name="user" id="user">
					<option value="">** Selecione um usu&aacute;rio **</option>
					<!-- BEGIN BLOCK_OPTION_USER -->
					<option value="{USERID}" {USUARIO_SELECIONA}>{USERNAME}</option>
					<!-- END BLOCK_OPTION_USER -->
				</select>
               </td>
               
               <td width="65">Periodo:</td>
               <td width="240" >
               <input name="data1" value="{DATA_INICIO}" id="data1" style="text-align: center" size="10" /> - <input id="data2" style="text-align: center" value="{DATA_FIM}" name="data2" size="10" />
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
                
              
                <div class="module">
                	<h2><span>Resultados</span></h2>
                  
                    <div class="module-table-body">
                   	  <form action="">
                        <table id="myTable" class="tablesorter" style="margin-bottom:0px">
                        	<thead>
                                <tr>
                                    <th style="width:20%">Vendedor</th>
                                    <th style="width:20%">Modalidade</th>
                                    <th style="width:21%">Anúncios</th>
                                    <th style="width:21%">Valor</th>
                                    <th style="width:21%">Lotacao</th>                                    
                                </tr>
                            </thead>
                            <tbody>
                                <!-- BEGIN BLOCK_DADOS -->
                                <tr>
                                    <td class="align-center">{CAPTADOR}</td>
                                    <td class="align-center">{MODALIDADE}</td>
                                    <td class="align-center">{NANUNCIO}</td>
                                    <td class="align-center">{VALOR}</td>
                                    <td>{DESCRICAO}</td>
                                </tr>
                                <!-- END BLOCK_DADOS -->
                            </tbody>
                        </table>
                        <table id="" class="">
                        	<thead>                                
                                <tr>
                                    <th class="align-center" width="41%" style="text-align:center;border-right:none">TOTAL PRODUÇÃO:</th>
                                    <th class="align-center" width="21%" style="text-align:center;border-right:none">{TOTALANUNCIOSPRODUCAO}</th>
                                    <th class="align-center" width="21%" style="text-align:center;border-right:none">R$ {TOTALGERALPRODUCAO}</th>
                                    
                                    <th class="align-center" width="30%" style="text-align:center;border-right:none"></th>
                                    <th class="align-center" width="30%" style="text-align:center"></th>
                                </tr>
								<tr>
                                    <th class="align-center" width="41%" style="text-align:center;border-right:none">TOTAL FINANCEIRO:</th>
                                    <th class="align-center" width="21%" style="text-align:center;border-right:none">{TOTALANUNCIOS}</th>
                                    <th class="align-center" width="21%" style="text-align:center;border-right:none">R$ {TOTALGERAL}</th>
                                    
                                    <th class="align-center" width="30%" style="text-align:center;border-right:none"></th>
                                    <th class="align-center" width="30%" style="text-align:center"></th>
                                </tr>
                            </thead>
                            
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
                        <h2><span>Resumo por vendedor (produção)</span></h2>
                        <table id="myTable2" class="tablesorter" style="margin-bottom:0px">
                        	<thead>
                                <tr>
                                    <th style="width:20%">Vendedor</th>
                                    <th style="width:20%">Anúncios</th>
                                    <th style="width:20%">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- BEGIN BLOCK_RESUMO_VENDEDORPROD -->
                                <tr>
                                    <td class="align-center">{VENDEDORESPROD}</td>
                                    <td class="align-center">{VENDEDORESANUNCIOSPROD}</td>
                                    <td class="align-center">R$ {TOTALVENDEDORPROD}</td>
                                </tr>
                                <!-- END BLOCK_RESUMO_VENDEDORPROD -->
                                
                            </tbody>
                        </table>
                        <table id="myTable3" class="">
                        	<thead>
                                <tr>
                                    <th class="align-center" width="33%" style="text-align:center;border-right:none">TOTAL:</th>
                                    <th class="align-center" width="33%" style="text-align:center;border-right:none">{TOTALVENDEDORESANUNCIOSPROD}</th>
                                    <th class="align-center" width="33%" style="text-align:center;border-right:none">R$ {TOTALVENDEDORESPROD}</th>
                                    
                                </tr>
                            </thead>
                            
                        </table>
                        <h2><span>Resumo por vendedor (financeiro)</span></h2>
                        <table id="myTable2" class="tablesorter" style="margin-bottom:0px">
                        	<thead>
                                <tr>
                                    <th style="width:20%">Vendedor</th>
                                    <th style="width:20%">Anúncios</th>
                                    <th style="width:20%">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- BEGIN BLOCK_RESUMO_VENDEDOR -->
                                <tr>
                                    <td class="align-center">{VENDEDORES}</td>
                                    <td class="align-center">{VENDEDORESANUNCIOS}</td>
                                    <td class="align-center">R$ {TOTALVENDEDOR}</td>
                                </tr>
                                <!-- END BLOCK_RESUMO_VENDEDOR -->
                                
                            </tbody>
                        </table>
                        <table id="myTable3" class="">
                        	<thead>
                                <tr>
                                    <th class="align-center" width="33%" style="text-align:center;border-right:none">TOTAL:</th>
                                    <th class="align-center" width="33%" style="text-align:center;border-right:none">{TOTALVENDEDORESANUNCIOS}</th>
                                    <th class="align-center" width="33%" style="text-align:center;border-right:none">R$ {TOTALVENDEDORES}</th>
                                    
                                </tr>
                            </thead>
                            
                        </table>
                        
                        
                        <h2><span>Resumo por modalidade</span></h2>
                        <table id="myTable4" class="tablesorter" style="margin-bottom:0px">
                        	<thead>
                                <tr>
                                    <th style="width:20%">Modalidade</th>
                                    <th style="width:20%">Anúncios</th>
                                    <th style="width:20%">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- BEGIN BLOCK_RESUMO_MODALIDADE -->
                                <tr>
                                    <td class="align-center">{MODALIDADES}</td>
                                    <td class="align-center">{MODALIDADESANUNCIOS}</td>
                                    <td class="align-center">R$ {TOTALMODALIDADE}</td>
                                </tr>
                                <!-- END BLOCK_RESUMO_MODALIDADE -->
                                
                            </tbody>
                        </table>
                        <table id="myTable5" class="">
                        	<thead>
                                <tr>
                                    <th class="align-center" width="33%" style="text-align:center;border-right:none">TOTAL:</td>
                                     <th class="align-center" width="33%" style="text-align:center;border-right:none"> {TOTALMODALIDADESANUNCIOS}</td>
                                    <th class="align-center" width="33%" style="text-align:center;border-right:none">R$ {TOTALMODALIDADES}</td>
                                </tr>
                            </thead>
                            
                        </table>
                        
                        <h2><span>Resumo contrato</span></h2>
                        <table id="myTable4" class="tablesorter" style="margin-bottom:0px">
                        	<thead>
                                <tr>
                                    <th style="width:20%">Captador</th>
                                    <th style="width:20%">Cliente</th>
                                    <th style="width:20%">Modalidade</th>
                                    <th style="width:20%">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- BEGIN BLOCK_RESUMO_CONTRATO -->
                                <tr>
                                    <td class="align-center">{CONCAPTADOR}</td>
                                    <td class="align-center">{CONCLIENTE}</td>
                                    <td class="align-center">{CONMODALIDADE}</td>
                                    <td class="align-center">R$ {CONVALOR}</td>
                                </tr>
                                <!-- END BLOCK_RESUMO_CONTRATO -->
                                
                            </tbody>
                        </table>
                        <table id="myTable5" class="">
                        	<thead>
                                <tr>
                                    <th class="align-center" width="33%" style="text-align:center;border-right:none">TOTAL:</td>
                                     <th class="align-center" width="33%" style="text-align:center;border-right:none"> </td>
                                    <th class="align-center" width="33%" style="text-align:center;border-right:none">R$ {CONTOTAL}</td>
                                </tr>
                            </thead>
                            
                        </table> 
                        
                        <div style="clear: both"></div>
                     </div> <!-- End .module-table-body -->
                     
                </div> 
                
                <!-- End .module -->
                 
                 
                 
                 
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