document.addEventListener('DOMContentLoaded', () => {
  const header = document.querySelector('.site-header');
  const toggle = document.getElementById('menuToggle');
  const mobileMenu = document.getElementById('mobileMenu');
  const closeBtn = document.getElementById('closeMenu');
  const track = document.getElementById('railTrack');

  // Header en scroll 
  const onScroll = () => {
    if (window.scrollY > 6) header?.classList.add('is-scrolled');
    else header?.classList.remove('is-scrolled');
  };
  onScroll();
  document.addEventListener('scroll', onScroll, { passive: true });

  // Menú móvil 
  const openMenu = () => {
    mobileMenu?.classList.add('open');
    toggle?.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  };
  const closeMenu = () => {
    mobileMenu?.classList.remove('open');
    toggle?.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  };
  toggle?.addEventListener('click', () => {
    const expanded = toggle.getAttribute('aria-expanded') === 'true';
    expanded ? closeMenu() : openMenu();
  });
  closeBtn?.addEventListener('click', closeMenu);
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && mobileMenu?.classList.contains('open')) closeMenu();
  });
  mobileMenu?.addEventListener('click', e => {
    const t = e.target;
    if (!(t instanceof HTMLElement)) return;
    if (t.tagName.toLowerCase() === 'a') closeMenu();
  });

  // Carrusel infinito (sin cambios)
  if (track) {
    const items = Array.from(track.children);
    items.forEach(it => track.appendChild(it.cloneNode(true)));
  }

  // Anno footer
  const y = document.getElementById('year');
  if (y) y.textContent = String(new Date().getFullYear());
});
// === HERO SLIDER  ===
document.addEventListener('DOMContentLoaded', () => {
  const slider = document.querySelector('.hero-slider');
  if (!slider) return;

  const track = slider.querySelector('.hero-track');
  const prev  = slider.querySelector('.hero-nav.prev');
  const next  = slider.querySelector('.hero-nav.next');
  const dots  = Array.from(slider.querySelectorAll('.hero-dots .dot'));


  const originals = Array.from(track.children);            
  const firstClone = originals[0].cloneNode(true);
  const lastClone  = originals[originals.length - 1].cloneNode(true);
  track.insertBefore(lastClone, track.firstChild);
  track.appendChild(firstClone);

  const total = track.children.length;                     
  const realTotal = originals.length;

  let pos = 1;
  let isJumping = false;

  // Coloca el carrusel en la 1ª real (no la del medio)
  const setTransform = (instant = false) => {
    track.style.transition = instant ? 'none' : 'transform .45s ease-in-out';
    track.style.transform  = `translateX(-${pos * 100}%)`;
  };

  const updateDots = () => {
    const realIndex = (pos - 1 + realTotal) % realTotal;  
    dots.forEach((d, i) => {
      d.classList.toggle('is-active', i === realIndex);
      d.setAttribute('aria-selected', i === realIndex ? 'true' : 'false');
      d.setAttribute('tabindex', i === realIndex ? '0' : '-1');
    });
  };

  const goTo = (newPos) => {
    pos = newPos;
    setTransform(false);
    updateDots();
  };


  track.addEventListener('transitionend', () => {
    if (isJumping) return;

    if (pos === total - 1) {
      isJumping = true;
      pos = 1;
      setTransform(true);

      void track.offsetWidth;
      isJumping = false;
    }

    if (pos === 0) {
      isJumping = true;
      pos = total - 2;
      setTransform(true);
      void track.offsetWidth;
      isJumping = false;
    }
  });


  next.addEventListener('click', () => goTo(pos + 1));
  prev.addEventListener('click', () => goTo(pos - 1));
  dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i + 1)));


  slider.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') goTo(pos + 1);
    if (e.key === 'ArrowLeft')  goTo(pos - 1);
  });

  
  let startX = 0, delta = 0, swiping = false;
  const onStart = (x) => { startX = x; swiping = true; };
  const onMove  = (x) => { if (!swiping) return; delta = x - startX; };
  const onEnd   = () => {
    if (!swiping) return;
    if (delta < -50) goTo(pos + 1);
    if (delta >  50) goTo(pos - 1);
    swiping = false; delta = 0;
  };
  slider.addEventListener('touchstart', (e) => onStart(e.touches[0].clientX), {passive:true});
  slider.addEventListener('touchmove',  (e) => onMove(e.touches[0].clientX),  {passive:true});
  slider.addEventListener('touchend',   onEnd);

  
  setTransform(true);
  updateDots();
});
