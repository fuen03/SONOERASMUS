// assets/js/universita.js
(function () {
  const data = (window.UNI_DATA || []).slice();

  const railTrack = document.getElementById('railTrack');
  const grid = document.getElementById('grid');
  const noResults = document.getElementById('noResults');
  const form = document.getElementById('uniSearchForm');
  const qInput = document.getElementById('q');

  // Rellena carrusel
  railTrack.innerHTML = data.map(u => `
    <figure class="rail-item">
      <a class="rail-link" href="universita-dettaglio.html?id=${encodeURIComponent(u.id)}" aria-label="${u.nome}">
        <div class="logo-placeholder">
          ${u.immagine ? <img src="${u.immagine}" alt="" loading="lazy"> : ``}
        </div>
        <figcaption>${u.nome}</figcaption>
      </a>
    </figure>
  `).join('');

  // Pinta el grid
  function render(list) {
    if (!list.length) {
      grid.innerHTML = '';
      noResults.hidden = false;
      return;
    }
    noResults.hidden = true;
    grid.innerHTML = list.map(u => `
      <article class="uni-card">
        <a class="uni-card-link" href="universita-dettaglio.html?id=${encodeURIComponent(u.id)}">
          <div class="uni-card-media">
            ${u.immagine ? <img src="${u.immagine}" alt="" loading="lazy"> : <div class="uni-card-ph"></div>}
          </div>
          <div class="uni-card-body">
            <h3 class="uni-card-title">${u.nome}</h3>
            <p class="uni-card-meta">${u.citta} · ${u.nazione}</p>
          </div>
        </a>
      </article>
    `).join('');
  }
  render(data);

  // Búsqueda
  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    const q = qInput.value.trim().toLowerCase();
    const filtered = !q ? data : data.filter(u =>
      u.nome.toLowerCase().includes(q) ||
      u.citta.toLowerCase().includes(q) ||
      u.nazione.toLowerCase().includes(q)
    );
    render(filtered);
    document.getElementById('risultati').scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
})();