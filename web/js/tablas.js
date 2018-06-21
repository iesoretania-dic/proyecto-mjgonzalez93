$(document).ready(function() {
    $("table thead th:last-child").data("sorter", false);

    $("table").tablesorter();
});