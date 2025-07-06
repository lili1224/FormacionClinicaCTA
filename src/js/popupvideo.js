document.querySelector('form').addEventListener('submit', function () {
  // Mostrar el popup
  document.getElementById('procesandoModal').style.display = 'flex';

  // Deshabilitar el botón de enviar
  this.querySelector('button[type=submit]').disabled = true;
});