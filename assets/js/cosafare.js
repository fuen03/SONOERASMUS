
(function(){

  const menu = document.querySelector('#mobileMenu');
  const openBtn = document.querySelector('[data-menu-toggle]');
  const closeBtn = document.querySelector('[data-menu-close]');
  if (menu && openBtn && closeBtn && !openBtn.dataset.bound) {
    const toggle = (open) => {
      menu.classList.toggle('open', open);
      openBtn.setAttribute('aria-expanded', String(open));
      if (open) closeBtn.focus();
    };
    openBtn.addEventListener('click', ()=>toggle(!menu.classList.contains('open')));
    closeBtn.addEventListener('click', ()=>toggle(false));
    // clic fuera
    menu.addEventListener('click', (e)=>{ if(e.target === menu) toggle(false); });
    openBtn.dataset.bound = '1';
  }


  const resultsUl = document.getElementById('cfResults');
  const form = document.getElementById('cfSearchForm');
  const input = document.getElementById('cfQuery');
  const chips = document.querySelectorAll('.chip');

  function filterList(query){
    const q = (query || '').toLowerCase().trim();
    resultsUl.querySelectorAll('.card').forEach(li=>{
      const text = li.innerText.toLowerCase();
      li.style.display = text.includes(q) ? '' : 'none';
    });
  }
  if (form) {
    form.addEventListener('submit', (e)=>{
      e.preventDefault();
      filterList(input.value);
    });
  }
  chips.forEach(ch=>{
    ch.addEventListener('click', ()=>{
      input.value = ch.textContent.trim();
      filterList(input.value);
    });
  });


  const monthEl = document.getElementById('calMonth');
  const tableEl = document.getElementById('calTable');
  if (monthEl && tableEl) {
    let ref = new Date(); // mes actual

    const MONTHS = ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno','Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'];
    function renderCal(date){
      const y = date.getFullYear();
      const m = date.getMonth();
      monthEl.textContent = `${MONTHS[m]} ${y}`;

      // arranca en lunes
      const first = new Date(y, m, 1);
      const start = (first.getDay() + 6) % 7; // 0=Mon
      const days = new Date(y, m+1, 0).getDate();

      let html = '<thead><tr>';
      ['Lun','Mar','Mer','Gio','Ven','Sab','Dom'].forEach(d=> html += <th scope="col">${d}</th>);
      html += '</tr></thead><tbody><tr>';

      for(let i=0;i<start;i++) html += '<td aria-hidden="true"></td>';

      const today = new Date();
      for(let d=1; d<=days; d++){
        const dt = new Date(y, m, d);
        const isToday = dt.toDateString() === today.toDateString();
        const hasEvent = (d % 5 === 0); // demo
        html += <td role="button" tabindex="0" class="${isToday?'is-today':''} ${hasEvent?'has-event':''}" data-day="${d}" aria-label="${d} ${MONTHS[m]} ${y}">${d}</td>;
        if ((start + d) % 7 === 0 && d !== days) html += '</tr><tr>';
      }

      const leftover = (start + days) % 7;
      for(let i=0;i<(leftover?7-leftover:0);i++) html += '<td aria-hidden="true"></td>';

      html += '</tr></tbody>';
      tableEl.innerHTML = html;

      tableEl.querySelectorAll('td[data-day]').forEach(cell=>{
        cell.addEventListener('click', ()=>filterByDay(cell.dataset.day, m, y));
        cell.addEventListener('keydown', (e)=>{ if(e.key==='Enter' || e.key===' ') { e.preventDefault(); cell.click(); }});
      });
    }

    function filterByDay(day, month, year){
      // filtro demo: muestra sólo cards cuyo número aparezca en el texto
      const q = ` ${day} `;
      resultsUl.querySelectorAll('.card').forEach(li=>{
        const show = li.innerText.includes(q) || Math.random()>.5; // simulación
        li.style.display = show ? '' : 'none';
      });
    }

    document.querySelector('[data-cal-prev]')?.addEventListener('click', ()=>{ ref.setMonth(ref.getMonth()-1); renderCal(ref); });
    document.querySelector('[data-cal-next]')?.addEventListener('click', ()=>{ ref.setMonth(ref.getMonth()+1); renderCal(ref); });

    renderCal(ref);
  }

})();