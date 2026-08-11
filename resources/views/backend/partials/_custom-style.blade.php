<style>
    .textTransform {
        animation: colorChange 40s infinite;
    }

    @keyframes colorChange {
        0% {
            color: #ff0000;
        }

        25% {
            color: #00ff00;
        }

        50% {
            color: #0000ff;
        }

        75% {
            color: #ff00ff;
        }

        100% {
            color: #ff0000;
        }
    }

    /* Global Dark & Gold Theme */
    body, .app-content, .main-content, .page, .main-container {
        background-color: #0B0B0B !important;
        color: #E0E0E0 !important;
    }

    .card, .box-shadow-primary, .box-shadow-secondary, .box-shadow-info, .box-shadow-warning {
        background-color: #161616 !important;
        border: 1px solid #2A2A2A !important;
        box-shadow: none !important;
    }

    .card-header, .card-footer {
        border-color: #2A2A2A !important;
        background-color: transparent !important;
    }

    h1, h2, h3, h4, h5, h6, .page-title, .card-title, .text-dark, .text-black, .fw-semibold {
        color: #FFFFFF !important;
    }

    .text-muted, p.text-muted, span.text-muted {
        color: #E0E0E0 !important;
    }

    /* Charts text override */
    .apexcharts-text, .apexcharts-title-text, .apexcharts-datalabel, .apexcharts-legend-text {
        fill: #E0E0E0 !important;
        color: #E0E0E0 !important;
    }
    
    .apexcharts-gridline {
        stroke: #2A2A2A !important;
    }

    .counter-icon.dash, .bg-primary, .bg-secondary, .bg-info, .bg-warning {
        background-color: #CFA267 !important;
        color: #121212 !important;
    }

    .counter-icon.dash svg {
        fill: #121212 !important;
    }

    /* Sidebar and Header Overrides */
    .app-sidebar, .app-header, .header-brand, .app-sidebar__logo, .sidebar-mini.sidenav-toggled .app-sidebar__logo, .side-header {
        background-color: #161616 !important;
        border-color: #2A2A2A !important;
    }
    .app-sidebar, .app-header {
        border-right: 1px solid #2A2A2A !important;
        border-bottom: 1px solid #2A2A2A !important;
    }
    
    /* Header Icons */
    .app-header .icon, .app-header svg, .app-header svg path, .app-header svg rect, .app-header i, .app-sidebar__toggle::before {
        color: #CFA267 !important;
        fill: #CFA267 !important;
    }
    .app-header .btn {
        background: transparent !important;
        border: none !important;
    }

    .side-menu__item, .nav-link, .header-brand-img {
        color: #E0E0E0 !important;
    }

    .side-menu__item:hover, .side-menu__item.active {
        color: #CFA267 !important;
    }

    .breadcrumb-item a {
        color: #CFA267 !important;
    }

    /* Tables */
    .table, .table td, .table th, .table-bordered, table.dataTable {
        color: #E0E0E0 !important;
        border-color: #2A2A2A !important;
    }
    .table td *, .table th * {
        color: inherit;
    }
    .table th {
        background-color: #2A2A2A !important;
        color: #CFA267 !important;
    }

    /* Form Controls (Search, inputs, selects) */
    .form-control, input, select, textarea, .dataTables_filter input, .dataTables_length select {
        background-color: #2A2A2A !important;
        color: #E0E0E0 !important;
        border: 1px solid #3A3A3A !important;
    }
    .form-control:focus, input:focus, select:focus {
        background-color: #2A2A2A !important;
        color: #FFFFFF !important;
        border-color: #CFA267 !important;
        box-shadow: none !important;
    }

    /* Footer */
    .footer, .app-footer {
        background-color: #0F0F0F !important;
        color: #707070 !important;
        border-top: 1px solid #2A2A2A !important;
    }

    /* Datatables pagination & info */
    .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_paginate .paginate_button {
        color: #A0A0A0 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current, 
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover,
    .page-item.active .page-link {
        background: #CFA267 !important;
        color: #121212 !important;
        border: 1px solid #CFA267 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover,
    .page-link:hover {
        background: #2A2A2A !important;
        color: #FFFFFF !important;
        border: 1px solid #2A2A2A !important;
    }
    
    .page-link, .page-item.disabled .page-link {
        background-color: #2A2A2A !important;
        color: #A0A0A0 !important;
        border-color: #3A3A3A !important;
    }

    /* Ensure specific white backgrounds are removed */
    .bg-white, .main-header {
        background-color: transparent !important;
    }
    .app-sidebar .bg-white {
        background-color: #161616 !important;
    }
</style>