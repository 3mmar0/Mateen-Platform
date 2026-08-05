<script>
(function(){
  try{
    var t=JSON.parse(localStorage.getItem('mateenCustomTheme')||'null');
    if(!t)return;
    var r=document.documentElement.style;
    if(t.greenDark)r.setProperty('--green-dark',t.greenDark);
    if(t.gold)r.setProperty('--gold',t.gold);
    if(t.beige)r.setProperty('--beige',t.beige);
    var patterns={
      stars:"url(\"data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23000' fill-opacity='0.045'%3E%3Cpath d='M20 15l1.5 4.5H26l-3.6 2.8 1.4 4.5-3.8-2.8-3.8 2.8 1.4-4.5L14 19.5h4.5z'/%3E%3C/g%3E%3C/svg%3E\")",
      geometric:"url(\"data:image/svg+xml,%3Csvg width='44' height='44' viewBox='0 0 44 44' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' stroke='%23000' stroke-opacity='0.05'%3E%3Cpath d='M22 2l20 20-20 20L2 22z'/%3E%3C/g%3E%3C/svg%3E\")",
      circles:"url(\"data:image/svg+xml,%3Csvg width='36' height='36' viewBox='0 0 36 36' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='18' cy='18' r='6' fill='none' stroke='%23000' stroke-opacity='0.05'%3E%3C/svg%3E\")"
    };
    var bg=patterns[t.pattern]||'';
    if(bg){
      document.addEventListener('DOMContentLoaded',function(){
        document.body.style.backgroundImage=bg;
        document.body.style.backgroundRepeat='repeat';
      });
    }
  }catch(e){}
})();
</script>
