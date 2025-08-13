<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomValidatePostSize
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set PHP settings for large file uploads
        ini_set('upload_max_filesize', '500M');
        ini_set('post_max_size', '500M');
        ini_set('max_execution_time', 300);
        ini_set('max_input_time', 300);
        ini_set('memory_limit', '512M');
        ini_set('max_file_uploads', 20);
        ini_set('max_input_vars', 3000);

        // Custom post size validation with higher limit (500MB)
        $max = $this->getPostMaxSize();
        
        if ($max > 0 && $request->server('CONTENT_LENGTH') > $max) {
            throw new \Illuminate\Http\Exceptions\PostTooLargeException;
        }

        return $next($request);
    }

    /**
     * Get the maximum POST size in bytes.
     *
     * @return int
     */
    protected function getPostMaxSize()
    {
        if ($this->isIniValueChangeable('post_max_size')) {
            return $this->returnBytes(ini_get('post_max_size'));
        }

        return 0;
    }

    /**
     * Determine if the given configuration option may be changed.
     *
     * @param  string  $option
     * @return bool
     */
    protected function isIniValueChangeable($option)
    {
        if (! function_exists('ini_get_all')) {
            return false;
        }

        $config = ini_get_all();

        return isset($config[$option]['access']) &&
               ($config[$option]['access'] & 2) === 2;
    }

    /**
     * Return the given value as bytes.
     *
     * @param  string  $value
     * @return int
     */
    protected function returnBytes($value)
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1] ?? '');
        $value = (int) $value;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }
} 