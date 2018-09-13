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
               <select name="time" id="time">
					<option value="">** Selecione uma lota&ccedil;&atilde;o **</option>
					<option value="996" {SELECTED_TODOS}>[ Todos TEM! Classificados ]</option>
					<option value="997" {SELECTED_LOJAS}>[ Todas as lojas do TEM! Classificados ]</option>
					<option value="998" {SELECTED_CALLCENTER}>[ Call Center ]</option>
					<option value="999" {SELECTED_ADBASEE}>[ Adbase-E ]</option>
					<!-- BEGIN BLOCK_OPTION_LOTACAO -->
					<option value="{TEAMID}" {SELECTED}>{DESCRICAO}</option>
					<!-- END BLOCK_OPTION_LOTACAO -->
				</select>               </td>
               
               <td>Periodo:</td>
               <td width="240" >
               <input name="data1" value="{DATA_INICIO}" id="data1" style="text-align: center" size="10" /> - <input id="data2" style="text-align: center" value="{DATA_FIM}" name="data2" size="10" />               </td>
               
               <td >
               <input type="submit" id="Buscar" value="Buscar" style="padding: 2px"/>               </td>
               </tr>
               <tr ><td colspan="5" style="color:#666"><br><b>*</b> Obs: se n&atilde;o for especificado um per&iacute;odo, a data considerada sempre ser&aacute; a atual</td></tr>
               </table>
               </form>
               </fieldset>
               
              <br><br>
               
               <div style="display: none;margin-top: 70px" id="load"><center><img src='../img/ajax-loader.gif' ></center></div>
                
               <div class="module">
                	<h2><span>Resultados</span></h2>
                  
                    <div class="module-table-body">
                    	<form action="">
                        <table id="myTable" class="tablesorter">
                        	<thead>
                                <tr>
                                    <th style="width:20%">Loja</th>
                                    <th style="width:20%">Valor em R$</th>
                                    <th style="width:21%">Cart&atilde;o/D&eacute;bito</th>
                                    <th style="width:21%">Compensacao/cortesia</th> 
                                    <th style="width:21%">Web</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- BEGIN BLOCK_DADOS -->
                                <tr>
                                    <td class="align-center">{LOJA}</td>
                                    <td class="align-center">{DINHEIRO}</td>
                                    <td class="align-center">{CARTAO}</td>
                                    <td class="align-center">{GRATIS}</td>
                                    <td class="align-center">{WEB}</td>
                                </tr>
                                <!-- END BLOCK_DADOS -->
                                
                                <tr>
								    <th class="align-center" scope="col">Total</th>
								    <th class="align-center" scope="col"><div align="center">{TOTALD}</div></th>
								    <th class="align-center" scope="col"><div align="center">{TOTALC}</div></th>
								    <th class="align-center" scope="col"><div align="center">{TOTALG}</div></th>
							        <th class="align-center" scope="col"><div align="center">{TOTALW}</div></th>
                                </tr>
                                
                                <tr>
								    <th scope="col">&nbsp;</th>
                                    <th scope="col">GERAL</th>
								    <th colspan="3" scope="col" align="center" style="text-align: center;">{GERAL}</th>
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
                                    <option value="10" >10</option>
                                    <option value="20">20</option>
                                    <option value="30" >30</option>
                                    <option value="40" >40</option>
									<option value="60" >60</option>
									<option value="80" >80</option>
									<option value="100" selected="selected">100</option>
                                </select>
                                </div>
                            </form>
                        </div>                       
                        <div style="clear: both"></div>
                     </div> <!-- End .module-table-body -->
                     
                </div> <!-- End .module -->
                 
			</div> <!-- End .grid_12 -->
                
            <!-- Categories list -->
            <div class="grid_6">
                
                <div style="clear:both;"></div>
			</div> <!-- End .grid_6 -->
            
        </div> <!-- End .container_12 -->
		
	</body>
</html>