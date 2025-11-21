// Basic starter JS
(function(){
  console.log('App JS loaded');
  // Simple helper to fetch JSON
  window.$getJSON = async function(url){
    const res = await fetch(url, {headers:{'Accept':'application/json'}});
    return res.json();
  }
})();
