document.getElementById('imagen').addEventListener('change', function(event) {
  const previewBox = document.querySelector('.preview-box');
  const previewImg = document.getElementById('preview-img');
  const file = event.target.files[0];

  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      previewImg.src = e.target.result;
      previewBox.style.display = 'block'; // Mostrar contenedor
    }
    reader.readAsDataURL(file);
  } else {
    previewImg.src = '';
    previewBox.style.display = 'none'; // Ocultar si no hay imagen
  }
});
