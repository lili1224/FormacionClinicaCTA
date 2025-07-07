document.addEventListener('DOMContentLoaded', () => {
  fetch('../DB/php/listacursos.php')
    .then(r => r.ok ? r.json() : Promise.reject(r))
    .then(lista => {
      const sel = document.getElementById('curso');
      sel.innerHTML = '<option value="" disabled selected>— seleccione —</option>';
      lista.forEach(({id, titulo}) => {
        sel.insertAdjacentHTML(
          'beforeend',
          `<option value="${id}">${titulo}</option>`
        );
      });
    })
    .catch(err => {
      console.error('Error cargando cursos', err);
      alert('No se pudieron cargar los cursos');
    });
});
