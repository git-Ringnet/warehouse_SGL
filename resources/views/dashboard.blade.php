<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SGL - Hệ thống quản lý kho</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --dark-color: #1e293b;
            --light-color: #f8fafc;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
        }

        body {
            font-family: "Roboto", sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }

        .dark {
            background-color: #0f172a;
            color: #f8fafc;
        }

        .sidebar {
            background: linear-gradient(180deg, #1a365d 0%, #0f2942 100%);
            transition: all 0.3s ease;
        }

        .sidebar .menu-item a {
            color: #fff;
            transition: all 0.3s ease;
        }

        .sidebar .menu-item a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .sidebar .menu-item a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #3b82f6;
            color: #fff;
        }

        .sidebar .nav-item {
            color: #fff;
            transition: all 0.3s ease;
        }

        .sidebar .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #3b82f6;
        }

        .sidebar .nav-item .flex {
            color: #fff;
        }

        .sidebar .dropdown-content a {
            color: #fff;
            transition: all 0.3s ease;
        }

        .sidebar .dropdown-content a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .dropdown-content a.active {
            background: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #3b82f6;
            color: #fff;
            font-weight: 500;
        }

        .sidebar .logo-text {
            color: #fff;
            font-weight: 600;
        }

        .sidebar .logo-icon {
            color: #fff;
        }

        .sidebar .search-input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #e2e8f0;
        }

        .sidebar .search-input::placeholder {
            color: #94a3b8;
        }

        .sidebar .user-info {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .user-info p {
            color: #e2e8f0;
        }

        .sidebar .user-info .role {
            color: #94a3b8;
        }

        .sidebar-collapsed {
            width: 70px;
        }

        .sidebar-collapsed .sidebar-text {
            display: none;
        }

        .sidebar-collapsed .logo-text {
            display: none;
        }

        .sidebar-collapsed .menu-item {
            justify-content: center;
        }

        .content-area {
            margin-left: 256px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                z-index: 1000;
                height: 100vh;
                width: 70px;
            }

            .content-area {
                margin-left: 0 !important;
            }
        }

        .table-responsive {
            overflow-x: auto;
        }

        .toast {
            animation: fadeIn 0.3s, fadeOut 0.5s 2.5s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
            }

            to {
                opacity: 0;
            }
        }

        .dropdown-content {
            display: none;
        }

        .dropdown-content.show {
            display: block;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .chart-container-lg {
            height: 350px;
        }

        .chart-tabs {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
        }

        .chart-tab {
            padding: 8px 16px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }

        .chart-tab.active {
            border-bottom-color: #3b82f6;
            color: #3b82f6;
            font-weight: 500;
        }

        .dark .chart-tabs {
            border-bottom-color: #374151;
        }

        .dark .chart-tab.active {
            border-bottom-color: #3b82f6;
            color: #3b82f6;
        }

        .progress-thin {
            height: 6px;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    <x-sidebar-component />
    <!-- Main Content -->
    <div class="content-area ml-64">
        <!-- Top Bar -->
        <header
            class="bg-white dark:bg-gray-800 shadow-sm py-4 px-6 flex justify-between items-center fixed top-0 right-0 left-0 z-40"
            style="left: 256px">
            <div class="flex items-center">
                <h1 class="text-xl font-bold text-gray-800 dark:text-white">
                    Tổng quan
                </h1>
            </div>

            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button id="userMenuToggle" class="flex items-center focus:outline-none">
                        <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User"
                            class="w-8 h-8 rounded-full mr-2" />
                        <span class="text-gray-700 dark:text-gray-300 hidden md:inline">Nguyễn Văn A</span>
                    </button>
                    <div
                        class="dropdown-menu absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg py-2 hidden z-50 border border-gray-200 dark:border-gray-700">
                        <a href="#"
                            class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700">Hồ
                            sơ</a>
                        <a href="#"
                            class="block px-4 py-2 text-gray-800 dark:text-gray-200 hover:bg-blue-50 dark:hover:bg-gray-700">Cài
                            đặt</a>
                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                        <a href="#"
                            class="block px-4 py-2 text-red-500 hover:bg-blue-50 dark:hover:bg-gray-700">Đăng xuất</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="pt-20 pb-16 px-6">
            <!-- Page Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">
                        Tổng quan hệ thống
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400">
                        Thống kê và báo cáo tổng quan - Cập nhật lúc
                        <span id="current-time"></span>
                    </p>
                </div>

                <div class="flex space-x-3 mt-4 md:mt-0">
                    <div class="relative">
                        <select id="timeRangeSelect"
                            class="appearance-none bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 pl-3 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="today">Hôm nay</option>
                            <option value="week">Tuần này</option>
                            <option value="month" selected>Tháng này</option>
                            <option value="year">Năm nay</option>
                            <option value="custom">Tùy chỉnh</option>
                        </select>
                        <div
                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 dark:text-gray-300">
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div
                            class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-500 dark:text-blue-300 mr-4">
                            <i class="fas fa-boxes text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Tổng vật tư
                            </p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                1,245
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div
                            class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-500 dark:text-purple-300 mr-4">
                            <i class="fas fa-sign-in-alt text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Nhập kho tháng
                            </p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                1,245
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div
                            class="p-3 rounded-full bg-orange-100 dark:bg-orange-900 text-orange-500 dark:text-orange-300 mr-4">
                            <i class="fas fa-sign-out-alt text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Xuất kho tháng
                            </p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                987
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Chart Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Inventory Overview Chart -->
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 lg:col-span-2 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Tổng quan tồn kho
                        </h3>
                    </div>
                    <div class="chart-container-lg">
                        <canvas id="inventoryOverviewChart"></canvas>
                    </div>
                </div>

                <!-- Warehouse Distribution Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Phân bố theo kho
                        </h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="warehouseDistributionChart"></canvas>
                    </div>
                    <div class="mt-4">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Kho chính</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">35%</span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Kho phụ</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">25%</span>
                        </div>
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Kho linh kiện</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">20%</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full bg-purple-500 mr-2"></div>
                                <span class="text-sm text-gray-600 dark:text-gray-300">Kho thành phẩm</span>
                            </div>
                            <span class="text-sm font-medium text-gray-800 dark:text-white">20%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secondary Chart Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Monthly Transactions Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Giao dịch hàng tháng
                        </h3>
                        <div class="text-blue-500 dark:text-blue-300 text-sm">
                            <i class="fas fa-calendar-alt mr-1"></i> 2023
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="monthlyTransactionsChart"></canvas>
                    </div>
                </div>

                <!-- Inventory Categories Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Phân loại vật tư
                        </h3>
                        <div class="text-blue-500 dark:text-blue-300 text-sm">
                            <i class="fas fa-tags mr-1"></i> Tất cả
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="inventoryCategoriesChart"></canvas>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-4 right-4 hidden">
        <div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>Đã cập nhật dữ liệu thành công!</span>
        </div>
    </div>

    <script>
        // Update current time
        function updateCurrentTime() {
            const now = new Date();
            const options = {
                weekday: "long",
                year: "numeric",
                month: "long",
                day: "numeric",
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit",
            };
            document.getElementById("current-time").textContent =
                now.toLocaleDateString("vi-VN", options);
        }

        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();

        // Dropdown Menus
        const dropdownToggles = document.querySelectorAll('[id$="Toggle"]');

        dropdownToggles.forEach((toggle) => {
            toggle.addEventListener("click", (e) => {
                e.stopPropagation();
                const menu = toggle.nextElementSibling;
                menu.classList.toggle("hidden");
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener("click", () => {
            document.querySelectorAll(".dropdown-menu").forEach((menu) => {
                menu.classList.add("hidden");
            });
        });

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll(".dropdown-content").forEach((menu) => {
            menu.addEventListener("click", (e) => {
                e.stopPropagation();
            });
        });

        // Chart tabs
        const chartTabs = document.querySelectorAll(".chart-tab");
        chartTabs.forEach((tab) => {
            tab.addEventListener("click", () => {
                // Remove active class from all tabs
                chartTabs.forEach((t) => t.classList.remove("active"));
                // Add active class to clicked tab
                tab.classList.add("active");

                // Here you would update the chart based on the selected tab
                // For demo purposes, we're just logging the tab data
                console.log(`Switched to ${tab.dataset.tab} view`);
            });
        });

        // Show Toast Notification
        setTimeout(() => {
            const toast = document.getElementById("toast");
            toast.classList.remove("hidden");

            setTimeout(() => {
                toast.classList.add("hidden");
            }, 3000);
        }, 2000);

        // Initialize Charts
        const initCharts = () => {
            const textColor = "#1f2937";
            const gridColor = "rgba(0, 0, 0, 0.1)";
            const borderColor = "rgba(0, 0, 0, 0.1)";

            // Inventory Overview Chart
            const inventoryOverviewCtx = document
                .getElementById("inventoryOverviewChart")
                .getContext("2d");
            const inventoryOverviewChart = new Chart(inventoryOverviewCtx, {
                type: "bar",
                data: {
                    labels: [
                        "Tháng 1",
                        "Tháng 2",
                        "Tháng 3",
                        "Tháng 4",
                        "Tháng 5",
                        "Tháng 6",
                    ],
                    datasets: [{
                            label: "Nhập kho",
                            data: [450, 500, 550, 600, 650, 700],
                            backgroundColor: "#10b981",
                            borderRadius: 4,
                        },
                        {
                            label: "Xuất kho",
                            data: [300, 350, 400, 450, 500, 550],
                            backgroundColor: "#ef4444",
                            borderRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "top",
                            labels: {
                                color: textColor,
                            },
                        },
                        tooltip: {
                            mode: "index",
                            intersect: false,
                        },
                        datalabels: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: textColor,
                            },
                            stacked: false,
                        },
                        y: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: textColor,
                            },
                            beginAtZero: true,
                            stacked: false,
                        },
                    },
                    interaction: {
                        intersect: false,
                        mode: "index",
                    },
                },
                plugins: [ChartDataLabels],
            });

            // Warehouse Distribution Chart
            const warehouseDistributionCtx = document
                .getElementById("warehouseDistributionChart")
                .getContext("2d");
            const warehouseDistributionChart = new Chart(warehouseDistributionCtx, {
                type: "doughnut",
                data: {
                    labels: ["Kho chính", "Kho phụ", "Kho linh kiện", "Kho thành phẩm"],
                    datasets: [{
                        data: [35, 25, 20, 20],
                        backgroundColor: ["#3b82f6", "#10b981", "#f59e0b", "#8b5cf6"],
                        borderWidth: 0,
                    }, ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw}%`;
                                },
                            },
                        },
                        datalabels: {
                            formatter: (value) => {
                                return `${value}%`;
                            },
                            color: "#fff",
                            font: {
                                weight: "bold",
                            },
                        },
                    },
                    cutout: "70%",
                },
                plugins: [ChartDataLabels],
            });

            // Monthly Transactions Chart
            const monthlyTransactionsCtx = document
                .getElementById("monthlyTransactionsChart")
                .getContext("2d");
            const monthlyTransactionsChart = new Chart(monthlyTransactionsCtx, {
                type: "line",
                data: {
                    labels: [
                        "Tháng 1",
                        "Tháng 2",
                        "Tháng 3",
                        "Tháng 4",
                        "Tháng 5",
                        "Tháng 6",
                    ],
                    datasets: [{
                        label: "Số giao dịch",
                        data: [65, 59, 80, 81, 56, 72],
                        fill: false,
                        borderColor: "#3b82f6",
                        backgroundColor: "#3b82f6",
                        tension: 0.4,
                        pointBackgroundColor: "#fff",
                        pointBorderColor: "#3b82f6",
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                    }, ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw}`;
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: textColor,
                            },
                        },
                        y: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: textColor,
                            },
                        },
                    },
                },
            });

            // Inventory Categories Chart
            const inventoryCategoriesCtx = document
                .getElementById("inventoryCategoriesChart")
                .getContext("2d");
            const inventoryCategoriesChart = new Chart(inventoryCategoriesCtx, {
                type: "polarArea",
                data: {
                    labels: [
                        "Linh kiện",
                        "Thành phẩm",
                    ],
                    datasets: [{
                        data: [45, 25],
                        backgroundColor: [
                            "rgba(59, 130, 246, 0.8)",
                            "rgba(16, 185, 129, 0.8)",
                            "rgba(245, 158, 11, 0.8)",
                            "rgba(139, 92, 246, 0.8)",
                            "rgba(239, 68, 68, 0.8)",
                        ],
                        borderWidth: 0,
                    }, ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "right",
                            labels: {
                                color: textColor,
                            },
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw}%`;
                                },
                            },
                        },
                    },
                    scales: {
                        r: {
                            grid: {
                                color: gridColor,
                            },
                            ticks: {
                                display: false,
                            },
                        },
                    },
                },
            });

            // Inventory Status Chart
            const inventoryStatusCtx = document
                .getElementById("inventoryStatusChart")
                .getContext("2d");
            const inventoryStatusChart = new Chart(inventoryStatusCtx, {
                type: "bar",
                data: {
                    labels: ["Linh kiện", "Thành phẩm", "Vật tư", "Bao bì", "Phụ kiện"],
                    datasets: [{
                            label: "Tồn kho",
                            data: [450, 250, 150, 100, 50],
                            backgroundColor: "#3b82f6",
                            borderRadius: 4,
                        },
                        {
                            label: "Tối thiểu",
                            data: [100, 80, 50, 30, 20],
                            backgroundColor: "#f59e0b",
                            borderRadius: 4,
                        },
                        {
                            label: "Tối đa",
                            data: [500, 400, 300, 200, 100],
                            backgroundColor: "#10b981",
                            borderRadius: 4,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "top",
                            labels: {
                                color: textColor,
                            },
                        },
                        tooltip: {
                            mode: "index",
                            intersect: false,
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: textColor,
                            },
                            stacked: false,
                        },
                        y: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: textColor,
                            },
                            beginAtZero: true,
                            stacked: false,
                        },
                    },
                },
            });

            return {
                inventoryOverviewChart,
                warehouseDistributionChart,
                monthlyTransactionsChart,
                inventoryCategoriesChart,
                inventoryStatusChart,
            };
        };

        // Update charts for dark mode
        const updateChartsForDarkMode = () => {
            const textColor = "#f8fafc";
            const gridColor = "rgba(255, 255, 255, 0.1)";
            const borderColor = "rgba(255, 255, 255, 0.2)";

            // Inventory Overview Chart
            charts.inventoryOverviewChart.options.scales.x.grid.color = gridColor;
            charts.inventoryOverviewChart.options.scales.x.ticks.color = textColor;
            charts.inventoryOverviewChart.options.scales.y.grid.color = gridColor;
            charts.inventoryOverviewChart.options.scales.y.ticks.color = textColor;
            charts.inventoryOverviewChart.options.plugins.legend.labels.color =
                textColor;
            charts.inventoryOverviewChart.update();

            // Warehouse Distribution Chart
            charts.warehouseDistributionChart.update();

            // Monthly Transactions Chart
            charts.monthlyTransactionsChart.options.scales.x.grid.color = gridColor;
            charts.monthlyTransactionsChart.options.scales.x.ticks.color =
                textColor;
            charts.monthlyTransactionsChart.options.scales.y.grid.color = gridColor;
            charts.monthlyTransactionsChart.options.scales.y.ticks.color =
                textColor;
            charts.monthlyTransactionsChart.update();

            // Inventory Categories Chart
            charts.inventoryCategoriesChart.options.plugins.legend.labels.color =
                textColor;
            charts.inventoryCategoriesChart.options.scales.r.grid.color = gridColor;
            charts.inventoryCategoriesChart.update();

            // Inventory Status Chart
            charts.inventoryStatusChart.options.scales.x.grid.color = gridColor;
            charts.inventoryStatusChart.options.scales.x.ticks.color = textColor;
            charts.inventoryStatusChart.options.scales.y.grid.color = gridColor;
            charts.inventoryStatusChart.options.scales.y.ticks.color = textColor;
            charts.inventoryStatusChart.options.plugins.legend.labels.color =
                textColor;
            charts.inventoryStatusChart.update();
        };

        // Update charts for light mode
        const updateChartsForLightMode = () => {
            const textColor = "#1f2937";
            const gridColor = "rgba(0, 0, 0, 0.1)";
            const borderColor = "rgba(0, 0, 0, 0.1)";

            // Inventory Overview Chart
            charts.inventoryOverviewChart.options.scales.x.grid.color = gridColor;
            charts.inventoryOverviewChart.options.scales.x.ticks.color = textColor;
            charts.inventoryOverviewChart.options.scales.y.grid.color = gridColor;
            charts.inventoryOverviewChart.options.scales.y.ticks.color = textColor;
            charts.inventoryOverviewChart.options.plugins.legend.labels.color =
                textColor;
            charts.inventoryOverviewChart.update();

            // Warehouse Distribution Chart
            charts.warehouseDistributionChart.update();

            // Monthly Transactions Chart
            charts.monthlyTransactionsChart.options.scales.x.grid.color = gridColor;
            charts.monthlyTransactionsChart.options.scales.x.ticks.color =
                textColor;
            charts.monthlyTransactionsChart.options.scales.y.grid.color = gridColor;
            charts.monthlyTransactionsChart.options.scales.y.ticks.color =
                textColor;
            charts.monthlyTransactionsChart.update();

            // Inventory Categories Chart
            charts.inventoryCategoriesChart.options.plugins.legend.labels.color =
                textColor;
            charts.inventoryCategoriesChart.options.scales.r.grid.color = gridColor;
            charts.inventoryCategoriesChart.update();

            // Inventory Status Chart
            charts.inventoryStatusChart.options.scales.x.grid.color = gridColor;
            charts.inventoryStatusChart.options.scales.x.ticks.color = textColor;
            charts.inventoryStatusChart.options.scales.y.grid.color = gridColor;
            charts.inventoryStatusChart.options.scales.y.ticks.color = textColor;
            charts.inventoryStatusChart.options.plugins.legend.labels.color =
                textColor;
            charts.inventoryStatusChart.update();
        };

        // Initialize all charts and store references
        const charts = initCharts();

        // Initialize with correct theme
        if (localStorage.getItem("theme") === "dark") {
            updateChartsForDarkMode();
        }

        // Time range selector
        const timeRangeSelect = document.getElementById("timeRangeSelect");
        timeRangeSelect.addEventListener("change", function() {
            // Here you would update the charts based on the selected time range
            // For demo purposes, we're just logging the selected value
            console.log(`Time range changed to: ${this.value}`);

            // Show loading state
            const toast = document.getElementById("toast");
            toast.innerHTML =
                '<div class="toast bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i><span>Đang tải dữ liệu...</span></div>';
            toast.classList.remove("hidden");

            // Simulate data loading
            setTimeout(() => {
                toast.innerHTML =
                    '<div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-check-circle mr-2"></i><span>Đã cập nhật dữ liệu thành công!</span></div>';

                setTimeout(() => {
                    toast.classList.add("hidden");
                }, 2000);
            }, 1500);
        });

        // Dropdown Menus
        function toggleDropdown(id) {
            const dropdown = document.getElementById(id);
            const allDropdowns = document.querySelectorAll(".dropdown-content");

            // Close all other dropdowns
            allDropdowns.forEach((d) => {
                if (d.id !== id) {
                    d.classList.remove("show");
                }
            });

            // Toggle current dropdown
            dropdown.classList.toggle("show");
        }

        // Close dropdowns when clicking outside
        document.addEventListener("click", (e) => {
            if (!e.target.closest(".dropdown")) {
                document.querySelectorAll(".dropdown-content").forEach((dropdown) => {
                    dropdown.classList.remove("show");
                });
            }
        });

        // Prevent dropdown from closing when clicking inside
        document.querySelectorAll(".dropdown-content").forEach((dropdown) => {
            dropdown.addEventListener("click", (e) => {
                e.stopPropagation();
            });
        });
    </script>
</body>

</html>
