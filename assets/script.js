const toggle=document.querySelector('.nav-toggle');
const nav=document.querySelector('.nav');
if(toggle&&nav){
  toggle.addEventListener('click',()=>{
    const open=nav.classList.toggle('open');
    toggle.setAttribute('aria-expanded',String(open));
  });
  nav.querySelectorAll('a').forEach(a=>a.addEventListener('click',()=>{
    nav.classList.remove('open');
    toggle.setAttribute('aria-expanded','false');
  }));
}

const observer=new IntersectionObserver((entries)=>{
  entries.forEach(e=>{
    if(e.isIntersecting){
      e.target.classList.add('is-visible');
      observer.unobserve(e.target);
    }
  });
},{threshold:.12});
document.querySelectorAll('.reveal').forEach(el=>observer.observe(el));

document.querySelectorAll('[data-scroll-top]').forEach(button=>{
  button.addEventListener('click',()=>{
    window.scrollTo({top:0,left:0,behavior:'smooth'});
    if(location.hash) history.replaceState(null,'',location.pathname+location.search);
  });
});

const lightbox=document.getElementById('image-lightbox');
const lightboxImage=lightbox?.querySelector('img');
const lightboxClose=lightbox?.querySelector('.lightbox-close');
let lastFocused=null;

function openLightbox(image){
  if(!lightbox||!lightboxImage) return;
  lastFocused=document.activeElement;
  lightboxImage.src=image.src;
  lightboxImage.alt=image.alt||'Инфографика';
  lightbox.classList.add('is-open');
  lightbox.setAttribute('aria-hidden','false');
  document.body.classList.add('modal-open');
  lightboxClose?.focus();
}
function closeLightbox(){
  if(!lightbox||!lightboxImage) return;
  lightbox.classList.remove('is-open');
  lightbox.setAttribute('aria-hidden','true');
  document.body.classList.remove('modal-open');
  lightboxImage.src='';
  if(lastFocused instanceof HTMLElement) lastFocused.focus();
}

document.querySelectorAll('.zoomable').forEach(image=>{
  image.addEventListener('click',()=>openLightbox(image));
  image.addEventListener('keydown',event=>{
    if(event.key==='Enter'||event.key===' '){
      event.preventDefault();
      openLightbox(image);
    }
  });
});
lightboxClose?.addEventListener('click',closeLightbox);
lightbox?.addEventListener('click',event=>{
  if(event.target===lightbox) closeLightbox();
});
document.addEventListener('keydown',event=>{
  if(event.key==='Escape'&&lightbox?.classList.contains('is-open')) closeLightbox();
});
