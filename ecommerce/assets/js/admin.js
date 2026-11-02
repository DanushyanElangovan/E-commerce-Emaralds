document.addEventListener('DOMContentLoaded', function () {
    // Initialize DataTables
    $('.datatable').DataTable({
        responsive: true,
        dom: '<"top"lf>rt<"bottom"ip>',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });

    // Toggle sidebar
    $('#sidebarToggle').click(function () {
        $('body').toggleClass('sidebar-toggled');
        $('.sidebar').toggleClass('toggled');
    });

    // Prevent empty search
    $('.search-form').submit(function (e) {
        if ($(this).find('input[type="search"]').val().trim() === '') {
            e.preventDefault();
        }
    });

    // Confirm before action
    $('.confirm-action').click(function (e) {
        if (!confirm($(this).data('confirm-message') || 'Are you sure?')) {
            e.preventDefault();
        }
    });

    // Initialize datepickers
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });

    // Toggle password visibility
    $('.toggle-password').click(function () {
        const input = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // Dynamic form fields
    $(document).on('click', '.add-form-field', function () {
        const template = $(this).data('template');
        const container = $(this).closest('.form-fields-container');
        const newField = $(template);
        container.append(newField);
    });

    $(document).on('click', '.remove-form-field', function () {
        if ($(this).closest('.form-fields-container').find('.form-field').length > 1) {
            $(this).closest('.form-field').remove();
        }
    });

    // Chart initialization
    if (typeof Chart !== 'undefined') {
        $('.chart-container').each(function () {
            const ctx = $(this).find('canvas')[0].getContext('2d');
            const chartData = JSON.parse($(this).data('chart-data'));

            new Chart(ctx, {
                type: chartData.type || 'bar',
                data: chartData.data,
                options: chartData.options || {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    }
});document.addEventListener('DOMContentLoaded', function () {
    // Initialize DataTables
    $('.datatable').DataTable({
        responsive: true,
        dom: '<"top"lf>rt<"bottom"ip>',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries",
            infoEmpty: "Showing 0 to 0 of 0 entries",
            infoFiltered: "(filtered from _MAX_ total entries)",
            paginate: {
                first: "First",
                last: "Last",
                next: "Next",
                previous: "Previous"
            }
        }
    });

    // Toggle sidebar
    $('#sidebarToggle').click(function () {
        $('body').toggleClass('sidebar-toggled');
        $('.sidebar').toggleClass('toggled');
    });

    // Prevent empty search
    $('.search-form').submit(function (e) {
        if ($(this).find('input[type="search"]').val().trim() === '') {
            e.preventDefault();
        }
    });

    // Confirm before action
    $('.confirm-action').click(function (e) {
        if (!confirm($(this).data('confirm-message') || 'Are you sure?')) {
            e.preventDefault();
        }
    });

    // Initialize datepickers
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });

    // Toggle password visibility
    $('.toggle-password').click(function () {
        const input = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // Dynamic form fields
    $(document).on('click', '.add-form-field', function () {
        const template = $(this).data('template');
        const container = $(this).closest('.form-fields-container');
        const newField = $(template);
        container.append(newField);
    });

    $(document).on('click', '.remove-form-field', function () {
        if ($(this).closest('.form-fields-container').find('.form-field').length > 1) {
            $(this).closest('.form-field').remove();
        }
    });

    // Chart initialization
    if (typeof Chart !== 'undefined') {
        $('.chart-container').each(function () {
            const ctx = $(this).find('canvas')[0].getContext('2d');
            const chartData = JSON.parse($(this).data('chart-data'));

            new Chart(ctx, {
                type: chartData.type || 'bar',
                data: chartData.data,
                options: chartData.options || {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    }
});