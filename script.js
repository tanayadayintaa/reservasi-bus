document.addEventListener("DOMContentLoaded", () => {
  const input = document.getElementById("searchInput");
  const form = document.getElementById("searchForm");
  if (!input || !form) return;

  function debounce(fn, delay=400){
    let t; return (...args)=>{clearTimeout(t);t=setTimeout(()=>fn.apply(this,args),delay);}
  }
  input.addEventListener("input", debounce(()=>{
    if (input.value.trim().length >= 2 || input.value.trim()==="") form.requestSubmit();
  },600));
});


