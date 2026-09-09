function nav_open() {
  document.getElementById("navBar").style.display = "block";
  document.getElementById("navBarButton").setAttribute('onclick','nav_close()');
  document.getElementById("navBarButton").innerHTML  = "<span class='material-symbols-outlined' id='navBarButton'>close</span>";
}

function nav_close() {
  document.getElementById("navBar").style.display = "none";
  document.getElementById("navBarButton").setAttribute('onclick','nav_open()');
  document.getElementById("navBarButton").innerHTML  = "<span class='material-symbols-outlined' id='navBarButton'>menu</span>";
}

function accordian(id) {
  var x = document.getElementById(id);
  if (x.className.indexOf("w3-show") == -1) {
    x.className += " w3-show";
  } else { 
    x.className = x.className.replace(" w3-show", "");
  }
}

function openTab(evt, tabName) {
  var i, x, tablinks;

  //hide all tab pages
  x = document.getElementsByClassName("tab");
  for (i = 0; i < x.length; i++) {
    x[i].style.display = "none";
  }

  //un "select" all tab buttons
  tablinks = document.getElementsByClassName("tablink");
  for (i = 0; i < x.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" w3-theme-d4", "");
  }

  //select and show current tab
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " w3-theme-d4";

  //update URL focus
    var url = new URL(document.URL);

    url.searchParams.set('focus', tabName);
    history.pushState(null, "", url.href);
}

function search(list) {
  // Declare variables
  var input, filter, ul, li, a, i, txtValue;
  input = document.getElementById(`Search_${list}`);
  filter = input.value.toUpperCase();
  ul = document.getElementById(list);
  li = ul.getElementsByTagName('li');

  // Loop through all list items, and hide those who don't match the search query
  for (i = 0; i < li.length; i++) {
    a = li[i].getElementsByTagName("a")[0];
    txtValue = a.textContent || a.innerText;
    if (txtValue.toUpperCase().indexOf(filter) > -1) {
      li[i].style.display = "";
    } else {
      li[i].style.display = "none";
    }
  }
}