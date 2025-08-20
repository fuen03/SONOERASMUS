// assets/js/universita-dettaglio.js
(function () {
  const params = new URLSearchParams(location.search);
  const id = params.get('id');

  const db = window.UNI_DATA || [];
  const uni = db.find(u => u.id === id) || db[0];

  // Elementos
  const pageTitle = document.getElementById('pageTitle');
  const bcCurrent = document.getElementById('bcCurrent');
  const name = document.getElementById('uniName');
  const meta = document.getElementById('uniMeta');
  const descr = document.getElementById('uniDescr');
  const img = document.getElementById('uniImage');
  const links = document.getElementById('uniLinks');

  // Pinta
  name.textContent = uni.nome;
  bcCurrent.textContent = uni.nome;
  pageTitle.textContent = ${uni.nome} – SonoErasmus+;
  meta.textContent = ${uni.citta} · ${uni.nazione};
  descr.textContent = uni.descrizione;
  if (uni.immagine) {
    img.src = uni.immagine;
    img.alt = Immagine di ${uni.nome};
  } else {
    img.alt = '';
  }
  links.innerHTML = (uni.links || []).map(l => <li><a href="${l.href}" target="_blank" rel="noopener">${l.label}</a></li>).join('');
})();