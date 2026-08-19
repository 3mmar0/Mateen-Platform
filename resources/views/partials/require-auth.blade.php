<script>
(function () {
  if (localStorage.getItem('mateen_api_token')) return;
  var next = location.pathname + location.search + location.hash;
  location.replace('/login?next=' + encodeURIComponent(next));
})();
</script>
