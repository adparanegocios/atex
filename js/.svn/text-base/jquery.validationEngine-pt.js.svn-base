(function($) {
	$.fn.validationEngineLanguage = function() {};
	$.validationEngineLanguage = {
		newLang: function() {
			$.validationEngineLanguage.allRules = 	{"required":{    			// Add your regex rules here, you can take telephone as an example
						"regex":"none",
						"alertText":"* campo obrigatorio!",
						"alertTextCheckboxMultiple":"* campo obrigatorio!",
						"alertTextCheckboxe":"* campo obrigatorio!"},
					"length":{
						"regex":"none",
						"alertText":"* Entre ",
						"alertText2":" e ",
						"alertText3": " caracteres permitidos!"},
					"maxCheckbox":{
						"regex":"none",
						"alertText":"* Marcações permitidas excedidas!"},	
					"minCheckbox":{
						"regex":"none",
						"alertText":"* Por favor selecione no mínimo ",
						"alertText2":" opção(ões)!"},	
					"confirm":{
						"regex":"none",
						"alertText":"* incorreto com a senha acima!"},		
					"telefone":{
						"regex":"/^[0-9\-\(\)\ ]+$/",
						"alertText":"* Telefone inválido!"},	
					"email":{
						//"regex":"/^[a-zA-Z0-9_\.\-]+\@([a-zA-Z0-9\-]+\.)+[a-zA-Z0-9]{2,4}$/",
						"regex":"/^[A-Za-z0-9]+([_.-][A-Za-z0-9]+)*@[A-Za-z0-9]+([_.-][A-Za-z0-9]+)*\\.[A-Za-z0-9]{2,3}$/",
						"alertText":"* Email incorreto!"},	
					"data":{
                         //"regex":"/^[0-9]{4}\-\[0-9]{1,2}\-\[0-9]{1,2}$/",
						 "regex":"/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4}$/",
                         "alertText":"* Data inválida, deve ser no formato DD/MM/AAAA!"},
					"numericoObrigatorio":{
						"regex":"/^[0-9\ ]+$/",
						"alertText":"* Apenas números!"},	
					"noSpecialCaracters":{
						//"regex":"/^[0-9_.a-zA-Z]+$/",
						"regex":"/^[A-Za-z0-9_.-]+$/",
						"alertText":"* Nenhum caractere especial é permitido!"},	
					"ajaxUser":{
						"file":"../includes/validaUsuario.php",
						"extraData":"name=eric",
						"alertTextOk":"* Este usuário está disponível!",	
						"alertTextLoad":"* Processando, por favor aguarde!",
						"alertText":"* Este usuário já existe!"},	
					"ajaxName":{
						"file":"../includes/validaUsuario.php",
						"extraData":"",
						"alertTextOk":"* Este usuário está disponível!",	
						"alertTextLoad":"* Processando, por favor aguarde!",
						"alertText":"* Este usuário já existe!"},
					"letraObrigatorio":{
						"regex":"/^[a-zA-Z\ \']+$/",
						"alertText":"* Apenas letras!"},
					"validate2fields":{
    					"nname":"validate2fields",
    					"alertText":"* Você deve infomar o primeiro e o último nome!"},
					"CEP":{
						"regex":"/^[0-9]{8}$/",
						"alertText":"* CEP inválido, use apenas números, em um total de 8 dígitos!"},
					"CPF":{
						"regex":"/^[0-9]{11}$/",
						"alertText":"* CPF inválido, use apenas números, em um total de 11 dígitos!"},
					"CNPJ":{
						"regex":"/^[0-9]{14}$/",
						"alertText":"* CNPJ inválido, use apenas números, em um total de 14 dígitos!"},
					"ajaxLOGIN":{
						//"regex":"/^[0-9]{11}$/",
						"file":"ajax/ValidaLogin.php",
						"extraData":"",
						"alertTextOk":"* Este LOGIN está disponível!",
						"alertTextLoad":"* Processando, por favor aguarde!",
						"alertText":"* Este LOGIN já existe!"},
					"ajaxEMAIL":{
						//"regex":"/^[0-9]{11}$/",
						"file":"includes/validaEMAIL.php",
						"extraData":"",
						"alertTextOk":"* Este EMAIL está disponível!",
						"alertTextLoad":"* Processando, por favor aguarde!",
						"alertText":"* Este EMAIL não está disponível, por favor tente outro EMAIL!"},
					"ajaxCPF":{
						"regex":"/^[0-9]{11}$/",
						"file":"includes/validaCPF.php",
						"extraData":"",
						"alertTextOk":"* Este CPF está válido!",
						"alertTextLoad":"* Processando, por favor aguarde!",
						"alertText":"* CPF inválido, use apenas números, em um total de 11 dígitos!"},
					"ajaxCNPJ":{
						"regex":"/^[0-9]{14}$/",
						"file":"includes/validaCNPJ.php",
						"extraData":"",
						"alertTextOk":"* Este CNPJ está válido!",
						"alertTextLoad":"* Processando, por favor aguarde!",
						"alertText":"* CNPJ inválido, use apenas números, em um total de 14 dígitos!"}
					}						
		}
	}
})(jQuery);

$(document).ready(function() {	
	$.validationEngineLanguage.newLang()
});