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
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
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

            <!-- Search Bar -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
                <form action="#" method="GET" class="flex flex-col md:flex-row gap-3">
                    <div class="flex-grow">
                        <div class="relative">
                            <input type="text" name="search" id="searchQuery" placeholder="Tìm kiếm theo ID hoặc Serial..." 
                                class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-blue-500 focus:border-blue-500 pl-10 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" />
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>
                    <div>
                        <select name="category" id="searchCategory" 
                            class="bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-4 pr-8 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-full md:w-auto">
                            <option value="all">Tất cả</option>
                            <option value="materials">Vật tư</option>
                            <option value="finished">Thành phẩm</option>
                            <option value="goods">Hàng hóa</option>
                            <option value="projects">Dự án</option>
                            <option value="customers">Khách hàng</option>
                        </select>
                    </div>
                    <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Tìm kiếm
                    </button>
                </form>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-500 dark:text-blue-300 mr-4">
                            <i class="fas fa-boxes text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Tổng nhập kho
                            </p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                3,248
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 dark:bg-red-900 text-red-500 dark:text-red-300 mr-4">
                            <i class="fas fa-sign-out-alt text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Tổng xuất kho
                            </p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                2,587
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-orange-100 dark:bg-orange-900 text-orange-500 dark:text-orange-300 mr-4">
                            <i class="fas fa-exclamation-triangle text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Tổng hư hỏng
                            </p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                345
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100 dark:bg-purple-900 text-purple-500 dark:text-purple-300 mr-4">
                            <i class="fas fa-project-diagram text-xl"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Tổng dự án
                            </p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-white">
                                78
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Chart Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Inventory Overview Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 lg:col-span-2 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Tổng quan nhập/xuất/hư hỏng
                        </h3>
                        <div class="flex items-center space-x-2">
                            <div class="flex items-center">
                                <button data-category="materials" class="category-filter px-3 py-1 text-xs rounded-full bg-blue-100 text-blue-800 active">Vật tư</button>
                            </div>
                            <div class="flex items-center">
                                <button data-category="finished" class="category-filter px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Thành phẩm</button>
                            </div>
                            <div class="flex items-center">
                                <button data-category="goods" class="category-filter px-3 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Hàng hóa</button>
                            </div>
                        </div>
                    </div>
                    <div class="chart-container-lg">
                        <canvas id="inventoryOverviewChart"></canvas>
                    </div>
                </div>

                <!-- Project Growth Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Mức độ gia tăng dự án
                        </h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="projectGrowthChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Secondary Chart Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Inventory Categories Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            Phân loại kho
                        </h3>
                        <div class="text-blue-500 dark:text-blue-300 text-sm">
                            <i class="fas fa-tags mr-1"></i> Tất cả
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="inventoryCategoriesChart"></canvas>
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
                        {
                            label: "Hư hỏng",
                            data: [50, 45, 60, 55, 65, 70],
                            backgroundColor: "#f59e0b",
                            borderRadius: 4,
                        }
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
                        title: {
                            display: true,
                            text: 'Vật Tư',
                            color: textColor,
                        }
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

            // Project Growth Chart
            const projectGrowthCtx = document
                .getElementById("projectGrowthChart")
                .getContext("2d");
            const projectGrowthChart = new Chart(projectGrowthCtx, {
                type: "line",
                data: {
                    labels: ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6"],
                    datasets: [{
                        label: "Số lượng dự án",
                        data: [12, 19, 25, 37, 45, 56],
                        fill: true,
                        backgroundColor: "rgba(139, 92, 246, 0.2)",
                        borderColor: "#8b5cf6",
                        tension: 0.4,
                        pointBackgroundColor: "#8b5cf6",
                        pointBorderColor: "#fff",
                        pointRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
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
                            }
                        },
                        y: {
                            grid: {
                                color: gridColor,
                                drawBorder: false,
                            },
                            ticks: {
                                color: textColor,
                                precision: 0
                            },
                            beginAtZero: true
                        }
                    }
                }
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
                projectGrowthChart
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

        // Category filter for Inventory Overview Chart
        document.querySelectorAll('.category-filter').forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                document.querySelectorAll('.category-filter').forEach(btn => {
                    btn.classList.remove('active', 'bg-blue-100', 'text-blue-800');
                    btn.classList.add('bg-gray-100', 'text-gray-800');
                });
                
                // Add active class to clicked button
                this.classList.add('active', 'bg-blue-100', 'text-blue-800');
                this.classList.remove('bg-gray-100', 'text-gray-800');
                
                const category = this.dataset.category;
                
                // Update chart title
                let title = '';
                switch (category) {
                    case 'materials':
                        title = 'Vật Tư';
                        break;
                    case 'finished':
                        title = 'Thành Phẩm';
                        break;
                    case 'goods':
                        title = 'Hàng Hóa';
                        break;
                }
                
                charts.inventoryOverviewChart.options.plugins.title.text = title;
                
                // Show loading state
                const toast = document.getElementById("toast");
                toast.innerHTML =
                    '<div class="toast bg-blue-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-spinner fa-spin mr-2"></i><span>Đang tải dữ liệu...</span></div>';
                toast.classList.remove("hidden");
                
                // Simulate data update (in production, you would fetch data from the server)
                setTimeout(() => {
                    // Update chart data with random values for demonstration
                    const newData = {
                        materials: {
                            input: [450, 500, 550, 600, 650, 700],
                            output: [300, 350, 400, 450, 500, 550],
                            damaged: [50, 45, 60, 55, 65, 70]
                        },
                        finished: {
                            input: [200, 220, 240, 260, 280, 300],
                            output: [180, 200, 220, 240, 260, 280],
                            damaged: [20, 25, 30, 35, 40, 45]
                        },
                        goods: {
                            input: [350, 370, 390, 410, 430, 450],
                            output: [320, 340, 360, 380, 400, 420],
                            damaged: [30, 35, 40, 45, 50, 55]
                        }
                    };
                    
                    charts.inventoryOverviewChart.data.datasets[0].data = newData[category].input;
                    charts.inventoryOverviewChart.data.datasets[1].data = newData[category].output;
                    charts.inventoryOverviewChart.data.datasets[2].data = newData[category].damaged;
                    charts.inventoryOverviewChart.update();
                    
                    toast.innerHTML =
                        '<div class="toast bg-green-500 text-white px-4 py-3 rounded-lg shadow-lg flex items-center"><i class="fas fa-check-circle mr-2"></i><span>Đã cập nhật dữ liệu thành công!</span></div>';
                    
                    setTimeout(() => {
                        toast.classList.add("hidden");
                    }, 2000);
                }, 1000);
            });
        });
    </script>
</body>

</html>
