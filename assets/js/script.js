$(document).ready(function () {
    // Toggle Sidebar
    $("#menu-toggle").click(function (e) {
        e.preventDefault();
        $(".wrapper").toggleClass("toggled");
    });

    // Initialize DataTables
    $('.datatable').DataTable({
        responsive: true,
        "pageLength": 10,
        "language": {
            "search": "Filter records:"
        }
    });
});
