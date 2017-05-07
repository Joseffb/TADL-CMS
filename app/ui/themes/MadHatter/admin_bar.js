loadExternalJS('https://unpkg.com/vue');
loadExternalJS('https://cdn.jsdelivr.net/vue.resource/1.3.1/vue-resource.min.js');
new Vue({
    el: '#tadl_admin_bar'
});
/* Set the width of the side navigation to 250px and the left margin of the page content to 250px and add a black background color to body */
function openNav() {
    document.getElementById("tadl_admin_wrapper").style.width = "500px";
    document.getElementById("vjs").style.marginRight = "500px";
    document.getElementById("tadl_openbtn").style.display = "none";
    document.getElementById("tadl_closebtn").style.display = "block";
    document.getElementById("tadl_admin_bar").style.display = "block";
    document.body.style.backgroundColor = "rgba(0,0,0,0.4)";
}

/* Set the width of the side navigation to 0 and the left margin of the page content to 0, and the background color of body to white */
function closeNav() {
    document.getElementById("tadl_admin_wrapper").style.width = "35px";
    document.getElementById("vjs").style.marginRight = "0";
    document.getElementById("tadl_openbtn").style.display = "block";
    document.getElementById("tadl_closebtn").style.display = "none";
    document.getElementById("tadl_admin_bar").style.display = "none";
    document.body.style.backgroundColor = "white";
}
