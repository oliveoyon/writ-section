const sidebarToggle = document.getElementById("sidebarToggle");
const sidebar = document.getElementById("sidebar");
const content = document.getElementById("content");
const header = document.querySelector(".header");
const overlay = document.getElementById("overlay");

// Toggle sidebar visibility
sidebarToggle.addEventListener("click", function () {
    if (window.innerWidth <= 1024) {
        sidebar.classList.toggle("show");
        overlay.style.display = sidebar.classList.contains("show")
            ? "block"
            : "none";
    } else {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("collapsed");
        header.classList.toggle("collapsed");

        if (sidebar.classList.contains("collapsed")) {
            header.style.left = "0";
            header.style.width = "100%";
            content.style.marginLeft = "0";
        } else {
            header.style.left = "250px";
            header.style.width = "calc(100% - 250px)";
            content.style.marginLeft = "250px";
        }
    }
});

overlay.addEventListener("click", function () {
    sidebar.classList.remove("show");
    overlay.style.display = "none";
});

// Handle multilevel menu toggle (Settings)
const submenuToggles = document.querySelectorAll(".sidebar .has-submenu");
submenuToggles.forEach(function (item) {
    item.addEventListener("click", function () {
        this.classList.toggle("show");
    });
});

// menu active

document.addEventListener('DOMContentLoaded', function() {
    // Get the current URL path (relative URL)
    const currentUrl = window.location.pathname;

    // Select all menu links
    const menuItems = document.querySelectorAll('.sidebar ul li a');

    menuItems.forEach(item => {
        const link = item.getAttribute('href');

        // Extract the relative path (ignore the domain and protocol)
        const linkPath = new URL(link, window.location.href).pathname;

        // Check if the current page URL matches this link's href
        if (currentUrl === linkPath && link !== '#') {
            // Add 'menu-active' to the parent <li> of the matched link
            item.closest('li').classList.add('menu-active');

            // If the link is inside a submenu, ensure the submenu is shown
            const parentLi = item.closest('.has-submenu');
            if (parentLi) {
                parentLi.classList.add('show');
                item.closest('ul').classList.add('submenu-active');
            }
        }
    });

    // Handle click event for submenus
    const submenuToggles = document.querySelectorAll('.sidebar .has-submenu > a');
    submenuToggles.forEach(function (item) {
        item.addEventListener('click', function (event) {
            const parentLi = this.closest('li');
            parentLi.classList.toggle('show');
        });
    });
});


// Handle the profile dropdown menu toggle
const profileButton = document.querySelector('.profile-button');
const dropdownMenu = document.querySelector('.dropdown-menu');

// Toggle the dropdown menu visibility on click
profileButton.addEventListener('click', function() {
    dropdownMenu.classList.toggle('show');
});

// Close the dropdown menu if clicked outside of it
document.addEventListener('click', function(event) {
    if (!profileButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
        dropdownMenu.classList.remove('show');
    }
});


document.addEventListener("DOMContentLoaded", function () {
    const submenus = document.querySelectorAll('.has-submenu > a');
    submenus.forEach((submenu) => {
        submenu.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent the default link behavior
            
            // Find the submenu and toggle its visibility
            const submenuList = submenu.nextElementSibling;
            const isVisible = submenuList.style.display === 'block';

            // Close all submenus
            document.querySelectorAll('.submenu').forEach((menu) => {
                menu.style.display = 'none';
            });

            // If it's not already open, open the clicked submenu
            if (!isVisible) {
                submenuList.style.display = 'block';
            }
        });
    });
});


