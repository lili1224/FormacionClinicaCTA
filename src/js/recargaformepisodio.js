window_onload = function(){
    if(location.search.indexOf('success=true')> -1){
        alert('El episodio se ha creado correctamente')
    }
    document.getElementById("episodio_form").reset();
}