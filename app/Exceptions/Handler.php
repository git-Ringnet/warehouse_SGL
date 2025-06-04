namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Exceptions\PostTooLargeException;

class Handler extends ExceptionHandler
{
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
        
        // Xử lý lỗi khi file upload quá lớn
        $this->renderable(function (PostTooLargeException $e) {
            return redirect()->back()->with('error', 'File tải lên quá lớn. Kích thước tối đa cho phép là 40MB. Vui lòng chọn file nhỏ hơn hoặc liên hệ quản trị viên.');
        });
    }
} 