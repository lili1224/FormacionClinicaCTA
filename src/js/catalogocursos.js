async function loadCourses() {
      try {
        const res = await fetch('../DB/php/cursos.php'); 
        const courses = await res.json();

        const container = document.getElementById('courses');
        container.innerHTML = '';

        courses.forEach((c) => {
          const card = document.createElement('a');
          card.href = `episodio.html?courseId=${c.id}`;
          card.className =
            'bg-white rounded-lg shadow hover:shadow-lg transition p-4 flex flex-col';

          card.innerHTML = `
            <img src="${c.thumbnail || 'https://placehold.co/640x360?text=Curso'}" alt="${c.title}" class="rounded-md mb-4 h-40 object-cover w-full" />
            <h2 class="text-xl font-semibold mb-2 truncate">${c.title}</h2>
            <p class="text-gray-600 line-clamp-3 flex-grow">${c.description}</p>
          `;

          container.appendChild(card);
        });
      } catch (err) {
        console.error('Error cargando cursos:', err);
      }
    }

    document.addEventListener('DOMContentLoaded', loadCourses);