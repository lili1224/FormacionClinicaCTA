window_onload = function(){
    if(location.search.indexOf('success=true')> -1){
        alert('El curso se ha creado correctamente')
    }
    document.getElementById("curso_form").reset();
}