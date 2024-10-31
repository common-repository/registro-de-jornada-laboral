jQuery(document).ready( function($){
	$('#registar').on('click', function(){  

    //console.log('Pulsado')


    var tipoRegistro = $("#registar").data("tipo-registro");
    //console.log(tipoRegistro);

     //La llamada AJAX
     $.ajax({
       type : "post",
           url : ficharme_vars.ajaxurl, // Pon aquí tu URL
           data : {
            action: 'ficharme_registrar_fichaje', 
            message : "Boton se ha pulsado",
            tipoRegistro: tipoRegistro
          },
          error: function(response){
           console.log(response);
         },
         success: function(response) {

           // console.log(tipoRegistro);

           // console.log('OK');
               // Actualiza el mensaje con la respuesta
               var obj = JSON.parse(response);

               $('.ultimo_fichaje').text(obj.ultimo_fichaje);

               $('#registar').text(obj.nuevo_tipo_fichaje);
               $('#registar').data('tipo-registro', obj.nuevo_tipo_fichaje);

             }
           })

   });
});