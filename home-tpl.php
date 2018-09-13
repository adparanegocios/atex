<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />

		<title>AdBase</title>
        
       
		<link rel="stylesheet" type="text/css" href="css/reset.css?v=2" tppabs="http://www.xooom.pl/work/magicadmin/css/reset.css" media="screen" />
       
		<link rel="stylesheet" type="text/css" href="css/grid.css" tppabs="http://www.xooom.pl/work/magicadmin/css/grid.css" media="screen" />
		
        <!-- IE Hacks for the Fluid 960 Grid System -->
        <!--[if IE 6]><link rel="stylesheet" type="text/css" href="ie6.css" tppabs="http://www.xooom.pl/work/magicadmin/css/ie6.css" media="screen" /><![endif]-->
		<!--[if IE 7]><link rel="stylesheet" type="text/css" href="ie.css" tppabs="http://www.xooom.pl/work/magicadmin/css/ie.css" media="screen" /><![endif]-->
        
        <link rel="stylesheet" type="text/css" href="css/styles.css?v=2" tppabs="http://www.xooom.pl/work/magicadmin/css/styles.css" media="screen" />
        
        <link rel="stylesheet" type="text/css" href="css/jquery.wysiwyg.css" tppabs="http://www.xooom.pl/work/magicadmin/css/jquery.wysiwyg.css" media="screen" />
        
        <link rel="stylesheet" type="text/css" href="css/tablesorter.css" tppabs="http://www.xooom.pl/work/magicadmin/css/tablesorter.css" media="screen" />
        
        <link rel="stylesheet" type="text/css" href="css/thickbox.css" tppabs="http://www.xooom.pl/work/magicadmin/css/thickbox.css" media="screen" />
        
        <link rel="stylesheet" href="css/validationEngine.jquery.css" type="text/css" media="screen" />
        
        <link rel="stylesheet" type="text/css" href="css/theme-blue.css" tppabs="http://www.xooom.pl/work/magicadmin/css/theme-blue.css" media="screen" />
       
        
		<!-- JQuery engine script-->
		<script type="text/javascript" src="js/jquery-1.4.2.min.js" tppabs="http://www.xooom.pl/work/magicadmin/js/jquery-1.3.2.min.js"></script>
        
		<!-- JQuery WYSIWYG plugin script -->
		<script type="text/javascript" src="js/jquery.wysiwyg.js" tppabs="http://www.xooom.pl/work/magicadmin/js/jquery.wysiwyg.js"></script>
        
        <!-- JQuery tablesorter plugin script-->
		<script type="text/javascript" src="js/jquery.tablesorter.min.js" tppabs="http://www.xooom.pl/work/magicadmin/js/jquery.tablesorter.min.js"></script>
        
		<!-- JQuery pager plugin script for tablesorter tables -->
		<script type="text/javascript" src="js/jquery.tablesorter.pager.js" tppabs="http://www.xooom.pl/work/magicadmin/js/jquery.tablesorter.pager.js"></script>
        
		<!-- JQuery password strength plugin script -->
		<script type="text/javascript" src="js/jquery.pstrength-min.1.2.js" tppabs="http://www.xooom.pl/work/magicadmin/js/jquery.pstrength-min.1.2.js"></script>
        
		<!-- JQuery thickbox plugin script -->
		<script type="text/javascript" src="js/thickbox.js" tppabs="http://www.xooom.pl/work/magicadmin/js/thickbox.js"></script>
                   
                <script type="text/javascript" src="js/jquery.validationEngine.js"></script>
                
                <script type="text/javascript" src="js/jquery.validationEngine-pt.js"></script> 
                
                <link href="css/jquery.click-calendario-1.0.css" rel="stylesheet" type="text/css"/>
        		<script language="javascript" src="js/jquery.click-calendario-1.0-min.js"></script>
        <!-- Initiate WYIWYG text area -->

                <script type="text/javascript">
			$(document).ready(function() { 

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
                
                $('#form').validationEngine();
                            
                            
				$("#myTable") 
				.tablesorter({
					// zebra coloring
					widgets: ['zebra'],
					// pass the headers argument and assing a object 
					headers: { 
						// assign the sixth column (we start counting zero) 
						6: { 
							// disable it by setting the property sorter to false 
							sorter: false 
						} 
					}
				}) 
			.tablesorterPager({container: $("#pager")}); 
		}); 
		</script>
        
        <script type="text/javascript">
			$(function()
			{
			$('#wysiwyg').wysiwyg(
			{
			controls : {
			separator01 : { visible : true },
			separator03 : { visible : true },
			separator04 : { visible : true },
			separator00 : { visible : true },
			separator07 : { visible : false },
			separator02 : { visible : false },
			separator08 : { visible : false },
			insertOrderedList : { visible : true },
			insertUnorderedList : { visible : true },
			undo: { visible : true },
			redo: { visible : true },
			justifyLeft: { visible : true },
			justifyCenter: { visible : true },
			justifyRight: { visible : true },
			justifyFull: { visible : true },
			subscript: { visible : true },
			superscript: { visible : true },
			underline: { visible : true },
            increaseFontSize : { visible : false },
            decreaseFontSize : { visible : false }
			}
			} );
			});
        </script>
        
        <!-- Initiate tablesorter script -->

        
        <!-- Initiate password strength script -->
		<script type="text/javascript">
			$(function() {

				
				
				
			$('.password').pstrength();
			});
        </script>
        <script>
        
        $(document).ready(function(){

        	
			$("#Buscar").click(function(){
				
				var user = $("#user").val(); 
				
				if(user == ""){
					alert("Selecione uma agencia!");
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
                <div class="container_12">
                    <div class="grid_8">
					&nbsp;
                    </div>
                    <div class="grid_4">
                        <a href="logout.php" id="logout">
                        Seja bem vindo, {USUARIO_LOGADO} | sair
                        </a>
                    </div>
                </div>
                <div style="clear:both;"></div>
                
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
                                                  
                        </ul>
                        
                    </div><!-- End. .grid_12-->
                    
                </div><!-- End. .container_12 -->
                
                <div style="clear: both;"></div>
                
            </div> <!-- End #subnav -->
            
        </div> <!-- End #header -->
        
		<div class="container_12">
        
            <div style="clear:both;"></div>
            
            
            
            <div class="grid_12">
                
              
               <center>
               <img style="margin-top: 50px" src="img/imagem-home.png" alt="" />
               </center>
                               
			</div> <!-- End .grid_12 -->
                
            <!-- Categories list -->
            <div class="grid_6">
                
                <div style="clear:both;"></div>
			</div> <!-- End .grid_6 -->
            
        </div> <!-- End .container_12 -->
		
	</body>
</html>