<?php

namespace Database\Seeders;

use App\Models\Software;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SoftwareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Đảm bảo thư mục software tồn tại
        if (!Storage::disk('public')->exists('software')) {
            Storage::disk('public')->makeDirectory('software');
        }

        // Tạo dữ liệu mẫu cho firmware
        $this->createFirmware();

        // Tạo dữ liệu mẫu cho ứng dụng di động
        $this->createMobileApps();

        // Tạo dữ liệu mẫu cho ứng dụng máy tính
        $this->createDesktopApps();

        // Tạo dữ liệu mẫu cho driver
        $this->createDrivers();
    }

    /**
     * Tạo dữ liệu mẫu cho firmware
     */
    private function createFirmware(): void
    {
        // Firmware 1
        Software::create([
            'name' => 'SGL IoT Gateway Firmware',
            'version' => '2.1.0',
            'type' => 'firmware',
            'file_path' => 'software/sgl-iot-gateway-firmware-2-1-0.bin',
            'file_name' => 'sgl_gateway_v2.1.0.bin',
            'file_size' => '12.45 MB',
            'file_type' => 'bin',
            'release_date' => Carbon::now()->subDays(15),
            'platform' => 'embedded',
            'status' => 'active',
            'description' => 'Firmware mới nhất cho thiết bị IoT Gateway của SGL, hỗ trợ kết nối với nhiều thiết bị cảm biến và truyền dữ liệu lên cloud.',
            'changelog' => "- Cải thiện tốc độ kết nối\n- Sửa lỗi bảo mật\n- Thêm hỗ trợ cho cảm biến nhiệt độ mới\n- Tối ưu hóa sử dụng pin",
            'download_count' => rand(50, 120)
        ]);

        // Firmware 2
        Software::create([
            'name' => 'SGL Sensor Module Firmware',
            'version' => '1.5.2',
            'type' => 'firmware',
            'file_path' => 'software/sgl-sensor-module-firmware-1-5-2.bin',
            'file_name' => 'sgl_sensor_v1.5.2.bin',
            'file_size' => '4.28 MB',
            'file_type' => 'bin',
            'release_date' => Carbon::now()->subDays(45),
            'platform' => 'embedded',
            'status' => 'active',
            'description' => 'Firmware cho module cảm biến SGL, sử dụng trong hệ thống giám sát môi trường và kho hàng.',
            'changelog' => "- Tăng độ chính xác của phép đo\n- Giảm tiêu thụ năng lượng\n- Sửa lỗi đọc giá trị cảm biến áp suất",
            'download_count' => rand(30, 100)
        ]);
    }

    /**
     * Tạo dữ liệu mẫu cho ứng dụng di động
     */
    private function createMobileApps(): void
    {
        // Mobile app 1
        Software::create([
            'name' => 'SGL Warehouse Manager',
            'version' => '3.2.1',
            'type' => 'mobile_app',
            'file_path' => 'software/sgl-warehouse-manager-3-2-1.apk',
            'file_name' => 'SGL_Warehouse_Manager_v3.2.1.apk',
            'file_size' => '45.60 MB',
            'file_type' => 'apk',
            'release_date' => Carbon::now()->subDays(7),
            'platform' => 'android',
            'status' => 'active',
            'description' => 'Ứng dụng quản lý kho hàng SGL trên nền tảng di động, giúp nhân viên kiểm kê và theo dõi hàng hóa mọi lúc mọi nơi.',
            'changelog' => "- Giao diện người dùng mới\n- Thêm tính năng quét mã QR để nhập/xuất kho\n- Cải thiện tốc độ đồng bộ dữ liệu\n- Sửa lỗi không hiển thị đúng tồn kho",
            'download_count' => rand(100, 250)
        ]);

        // Mobile app 2
        Software::create([
            'name' => 'SGL Inventory Scanner',
            'version' => '2.0.5',
            'type' => 'mobile_app',
            'file_path' => 'software/sgl-inventory-scanner-2-0-5.apk',
            'file_name' => 'SGL_Inventory_Scanner_v2.0.5.apk',
            'file_size' => '32.18 MB',
            'file_type' => 'apk',
            'release_date' => Carbon::now()->subDays(60),
            'platform' => 'android',
            'status' => 'active',
            'description' => 'Ứng dụng chuyên dụng cho việc quét mã vạch và kiểm kê nhanh chóng trong kho hàng SGL.',
            'changelog' => "- Tối ưu hóa thuật toán nhận diện mã vạch\n- Thêm khả năng làm việc offline\n- Đồng bộ hóa dữ liệu tự động khi có kết nối\n- Sửa lỗi crash khi quét mã vạch bị mờ",
            'download_count' => rand(80, 180)
        ]);
    }

    /**
     * Tạo dữ liệu mẫu cho ứng dụng máy tính
     */
    private function createDesktopApps(): void
    {
        // Desktop app 1
        Software::create([
            'name' => 'SGL Warehouse Control System',
            'version' => '4.5.0',
            'type' => 'desktop_app',
            'file_path' => 'software/sgl-warehouse-control-system-4-5-0.exe',
            'file_name' => 'SGL_WCS_Setup_v4.5.0.exe',
            'file_size' => '156.72 MB',
            'file_type' => 'exe',
            'release_date' => Carbon::now()->subDays(20),
            'platform' => 'windows',
            'status' => 'active',
            'description' => 'Phần mềm quản lý kho hàng toàn diện trên máy tính, tích hợp với tất cả các thiết bị và cảm biến trong hệ thống kho SGL.',
            'changelog' => "- Thêm module báo cáo phân tích dữ liệu\n- Cải thiện giao diện người dùng\n- Hỗ trợ xuất báo cáo nhiều định dạng hơn\n- Tối ưu hóa sử dụng cơ sở dữ liệu",
            'download_count' => rand(40, 90)
        ]);

        // Desktop app 2
        Software::create([
            'name' => 'SGL Data Analyzer',
            'version' => '2.3.4',
            'type' => 'desktop_app',
            'file_path' => 'software/sgl-data-analyzer-2-3-4.zip',
            'file_name' => 'SGL_Data_Analyzer_v2.3.4.zip',
            'file_size' => '78.35 MB',
            'file_type' => 'zip',
            'release_date' => Carbon::now()->subDays(90),
            'platform' => 'windows',
            'status' => 'active',
            'description' => 'Công cụ phân tích dữ liệu chuyên sâu cho hệ thống kho hàng SGL, giúp tối ưu hóa quy trình và đưa ra dự báo.',
            'changelog' => "- Thêm các thuật toán dự báo mới\n- Cải thiện hiệu suất xử lý dữ liệu lớn\n- Thêm biểu đồ và báo cáo trực quan\n- Sửa lỗi trong module xuất dữ liệu",
            'download_count' => rand(25, 70)
        ]);
    }

    /**
     * Tạo dữ liệu mẫu cho driver
     */
    private function createDrivers(): void
    {
        // Driver 1
        Software::create([
            'name' => 'SGL Barcode Scanner Driver',
            'version' => '1.2.0',
            'type' => 'driver',
            'file_path' => 'software/sgl-barcode-scanner-driver-1-2-0.zip',
            'file_name' => 'SGL_Scanner_Driver_v1.2.0.zip',
            'file_size' => '8.45 MB',
            'file_type' => 'zip',
            'release_date' => Carbon::now()->subDays(120),
            'platform' => 'windows',
            'status' => 'active',
            'description' => 'Driver cho máy quét mã vạch sử dụng trong hệ thống kho hàng SGL, hỗ trợ kết nối USB và Bluetooth.',
            'changelog' => "- Thêm hỗ trợ cho model máy quét mới\n- Cải thiện tốc độ nhận dạng\n- Sửa lỗi kết nối Bluetooth không ổn định",
            'download_count' => rand(60, 150)
        ]);

        // Driver 2
        Software::create([
            'name' => 'SGL Label Printer Driver',
            'version' => '2.0.1',
            'type' => 'driver',
            'file_path' => 'software/sgl-label-printer-driver-2-0-1.zip',
            'file_name' => 'SGL_Label_Printer_Driver_v2.0.1.zip',
            'file_size' => '15.22 MB',
            'file_type' => 'zip',
            'release_date' => Carbon::now()->subDays(180),
            'platform' => 'windows',
            'status' => 'active',
            'description' => 'Driver cho máy in nhãn trong hệ thống kho hàng SGL, hỗ trợ nhiều loại nhãn và mã vạch.',
            'changelog' => "- Hỗ trợ thêm nhiều định dạng nhãn mới\n- Tối ưu hóa tốc độ in\n- Thêm tính năng tự động cắt nhãn\n- Sửa lỗi in mã QR",
            'download_count' => rand(40, 100)
        ]);

        // Beta driver
        Software::create([
            'name' => 'SGL RFID Reader Driver',
            'version' => '0.9.5',
            'type' => 'driver',
            'file_path' => 'software/sgl-rfid-reader-driver-0-9-5.zip',
            'file_name' => 'SGL_RFID_Driver_Beta_v0.9.5.zip',
            'file_size' => '12.87 MB',
            'file_type' => 'zip',
            'release_date' => Carbon::now()->subDays(10),
            'platform' => 'windows',
            'status' => 'beta',
            'description' => 'Driver cho đầu đọc RFID mới của hệ thống kho hàng SGL, đang trong giai đoạn thử nghiệm.',
            'changelog' => "- Phiên bản beta đầu tiên\n- Hỗ trợ đọc thẻ RFID tần số cao và thấp\n- Tích hợp với phần mềm quản lý kho SGL\n- Cần phản hồi về các lỗi gặp phải",
            'download_count' => rand(10, 30)
        ]);
    }
} 