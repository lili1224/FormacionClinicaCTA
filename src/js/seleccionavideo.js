fetch('DB/php/lista_videos.php')
  .then(r => r.ok ? r.json() : [])
  .then(lista => {
      const sel = document.getElementById('video');
      sel.innerHTML = '<option value="">-- seleccione --</option>';
      lista.forEach(v => sel.append(new Option(v, v)));
  })
  .catch(() => alert('No se pudo cargar la lista de vídeos'));