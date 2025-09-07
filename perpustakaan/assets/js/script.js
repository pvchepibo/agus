// Script ini akan berjalan setelah semua elemen HTML dimuat
document.addEventListener("DOMContentLoaded", function() {
    
    // Dapatkan nama file halaman saat ini (contoh: "dashboard.php")
    const currentPage = window.location.pathname.split('/').pop();

    // Dapatkan semua link di dalam menu sidebar
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');

    // Loop melalui setiap link
    sidebarLinks.forEach(link => {
        const linkPage = link.getAttribute('href');

        // Jika nama file di atribut href link sama dengan halaman saat ini
        if (linkPage === currentPage) {
            // Tambahkan kelas 'active' ke link tersebut
            link.classList.add('active');
        }
    });
});
