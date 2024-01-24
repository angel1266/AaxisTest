$(document).ready(function(){
   loadData();
 });

 function loadData(){
    var twk = $("#twk").val();
 $.ajax({
        type: 'get',
        url: '/api/list/product',
        //data: JSON.stringify(SendInfo),
        contentType: "application/json; charset=utf-8",
        traditional: true,
         headers: {
            "Authorization": "Bearer "+twk
        },
        success: function (data) {
            console.log(data);
            var content = "";
            if(data.length > 0){
               for(var i = 0; i < data.length; i++){
                  content = content + "<tr>"
                            +'<td>'+data[i]["sku"]+'</td>'
                            +'<td>'+data[i]["productName"]+'</td>'
                            +'<td>'+data[i]["description"]+'</td>'
                            +'<td><button type="button" class="btn btn-info" data-bs-target="#modal" data-bs-toggle="modal" id="edit" idp="'+data[i]["id"]+'" sku="'+data[i]["sku"]+'" pname="'+data[i]["productName"]+'" description="'+data[i]["description"]+'" taction="edit">Editar</button></td>'
                            +"</tr>";
               }

               $("#contentTable").html(content);
            } 
        }
    });
 }
   
   $(document).on("click", "#edit",function(){
      var sku             = $(this).attr("sku");
      var name            = $(this).attr("pname");
      var description     = $(this).attr("description");
      var idp             = $(this).attr("idp");
      var taction         = $(this).attr("taction");
      $("#title-modal").text("Editar Producto");
      $("#sku").val(sku);
      $("#nombre").val(name);
      $("#descripcion").val(description);
      $("#idproducto").val(idp);
      $("#btn-guardar").attr("taction",taction);
   });
   $(document).on("click", "#new-product",function(){
      var sku         = $(this).attr("sku");
      var name        = $(this).attr("pname");
      var description = $(this).attr("description");
      idp             = $(this).attr("idp");
      var taction         = $(this).attr("taction");
      $("#title-modal").text("Crear Producto");
      $("#sku").val("");
      $("#nombre").val("");
      $("#descripcion").val("");
      $("#idproducto").val("");
      $("#btn-guardar").attr("taction",taction);
   });

$(document).on("click", "#btn-guardar",function(){
      var twk = $("#twk").val();

      var sku         = $("#sku").val();
      var name        = $("#nombre").val();
      var description = $("#descripcion").val();
      var id          = $("#idproducto").val();
      var taction     = $(this).attr("taction");
      $("#loaderModal").html("Procesando...");
      $("#box-content").css("display","none");

      $.ajax({
        type: 'post',
        url: taction == "edit" ? '/api/update/products' : '/api/create/products',
        data: taction == "edit" ? JSON.stringify({"products":[{
            "id":id,
            "sku": sku,
            "product_name": name,
            "description": description
        }]})
        : JSON.stringify({"products":[{
            "sku": sku,
            "product_name": name,
            "description": description
        }]}),
        contentType: "application/json; charset=utf-8",
        traditional: true,
         headers: {
            "Authorization": "Bearer "+twk
        },
        success: function (data, textStatus, xhr) {
            $("#loaderModal").html("");
            $("#box-content").css("display","block");
            console.log(textStatus);

            if (textStatus == "success") {
               loadData();
              $("#btn-close").click();
              alertify.success(data["message"]);
            }
        }
    }).fail(function (data, textStatus, error) {
        $("#loaderModal").html("");
        $("#box-content").css("display","block");
        alertify.error(data.responseJSON["error"]);
    });
   });
