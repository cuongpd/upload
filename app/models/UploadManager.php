<?php

/**
 * Class sử dụng cho việc Upload ảnh trên hệ thống
 */
class UploadManager extends Eloquent {
    /*
     * Upload File của hệ thống Laravel
     * @input $file_form : tên upload file định nghĩa trên Form
     * @input $file_name : tên file sẽ được lưu trữ trên server
     * @input $key : thuộc tính upload ảnh, cấu hình trong app/config/upload.config, mặc định sẽ đưa vào gallery
     * @input $created : thời gian upload, dùng để đặt kèm trong tên file và tạo đường dẫn sub giúp quản lý tốt việc Upload
     * @input $postion : vị trí đóng dấu ảnh, nếu truyền lên = 0 , không xử lý đóng dấu ảnh.
     * Vị trí postion định nghĩa ở hàm WatermarkImage
     */

    static public function UploadFile($file_form, $file_name = 'cimage', $category = 'gallery', $created = TIME_NOW, $postion = 3) {
        $image_default = '';
        /* Upload To Server */
        if (Input::hasFile($file_form)) {
            $upload = Input::file($file_form);
            $file_name = FunctionLib::safe_title($file_name);
            /* Upload ảnh ban đầu và lưu trong thư mục origin */
            $dir_upload_image = self::getDirUpload($category, $created);
            $dir_upload_image_origin = $dir_upload_image . Config::get('cuploads.dir_upload_origin');
            $image_default = $file_name . '-' . $created . '.' . $upload->getClientOriginalExtension();

            $input = $dir_upload_image_origin . $image_default;
            try {
                $has_upload = $upload->move($dir_upload_image_origin, $image_default);
            } catch (Exception $e) {
                Log::error($e);
                $has_upload = '';
            }

            /* Nếu upload được ảnh thì tiếp tục xử lý */
            if ($has_upload) {
                $output = '';
                /* Kiểm tra có đóng dấu ảnh hay không */
                if ($postion) {
                    $output = $dir_upload_image . $image_default;
                    self::WatermarkImage($input, $output, $postion);
                    $input = $output;
                }
                /* Resize kích thước ảnh */
                $resize_config = self::getUploadConfig($category);
                if ($resize_config) {
                    foreach ($resize_config as $key => $key_resize) {
                        $resize_upload_dir = self::getDirUpload($category, $created, $key);
                        $resize = array();
                        if ($key_resize['width'] > 0) {
                            $resize['width'] = $key_resize['width'];
                        }
                        if ($key_resize['height'] > 0) {
                            $resize['height'] = $key_resize['height'];
                        }
                        Image::make($input, $resize)->save($resize_upload_dir . $image_default);
                    }
                }
                if ($output) {
                    @unlink($input);
                }
            }
        }
        //echo UploadImages::getImageUrl($image_default, $category, 120, $created);
        return $image_default;
    }

    static public function UploadUrl($image_url, $file_name = 'cimage', $category = 'default', $created = TIME_NOW, $postion = 3) {
        $image_default = '';
        $ext = FunctionLib::getExtension($image_url, 'jpg');
        /* Kiểm tra xem ảnh có tồn tại hay không */
        if (self::checkImageUrl($image_url)) {
            /* Upload ảnh ban đầu và lưu trong thư mục origin */
            $dir_upload_image = self::getDirUpload($category, $created);
            $dir_upload_image_origin = $dir_upload_image . Config::get('cuploads.dir_upload_origin');
            $image_default = $file_name . '-' . $created . '.' . $ext;
            try {
                $remote_upload_image = $dir_upload_image_origin . $image_default;
                //self::save_image($image_url, $remote_upload_image);
                self::makeDir($dir_upload_image_origin);
                file_put_contents($remote_upload_image, file($image_url));
                $input = $remote_upload_image;
            } catch (Exception $e) {
                Log::error($e);
                $input = '';
            }
            /* Nếu upload được ảnh thì tiếp tục xử lý */
            if ($input) {
                $output = '';
                /* Kiểm tra có đóng dấu ảnh hay không */
                if ($postion) {
                    $output = $dir_upload_image . $image_default;
                    self::WatermarkImage($input, $output, $postion);
                    $input = $output;
                }
                /* Resize kích thước ảnh */
                $resize_config = self::getUploadConfig($category);
                if ($resize_config) {
                    foreach ($resize_config as $key => $key_resize) {
                        $resize_upload_dir = self::getDirUpload($category, $created, $key);
                        $resize = array();
                        if ($key_resize['width'] > 0) {
                            $resize['width'] = $key_resize['width'];
                        }
                        if ($key_resize['height'] > 0) {
                            $resize['height'] = $key_resize['height'];
                        }
                        Image::make($input, $resize)->save($resize_upload_dir . $image_default);
                    }
                }
                if ($output) {
                    @unlink($input);
                }
            }
        }
        //echo UploadImages::getImageUrl($image_default, $category, 120, $created);
        return $image_default;
    }

    /*
     * Upload File của hệ thống Laravel
     * @input $file_form : tên upload file định nghĩa trên Form
     * @input $file_name : tên file sẽ được lưu trữ trên server
     * @input $key : thuộc tính upload ảnh, cấu hình trong app/config/upload.config, mặc định sẽ đưa vào gallery
     * @input $created : thời gian upload, dùng để đặt kèm trong tên file và tạo đường dẫn sub giúp quản lý tốt việc Upload
     * @input $postion : vị trí đóng dấu ảnh, nếu truyền lên = 0 , không xử lý đóng dấu ảnh.
     * Vị trí postion định nghĩa ở hàm WatermarkImage
     */

    static public function SaveImageUrl($image_url, $file_name = 'cimage', $postion = 3) {
        $image_default = '';
        $ext = FunctionLib::getExtension($image_url, 'jpg');
        $created = TIME_NOW;
        $key_upload = Config::get('cuploads.upload_image_from_url_dir'); /* downloads */
        $size_upload = Config::get('cuploads.upload_image_from_url_resize'); /* 640 */

        if (self::checkImageUrl($image_url)) {
            /* Upload ảnh ban đầu và lưu trong thư mục origin */
            $dir_upload_image = self::getDirUpload($key_upload, $created);
            $dir_upload_image_origin = $dir_upload_image . Config::get('cuploads.dir_upload_origin');
            $image_default = $file_name . '-' . $created . '.' . $ext;
            try {
                $remote_upload_image = $dir_upload_image_origin . $image_default;
                self::makeDir($dir_upload_image_origin);
                file_put_contents($remote_upload_image, file($image_url));
                $input = $remote_upload_image;
            } catch (Exception $e) {
                Log::error($e);
                $input = '';
            }
            /* Nếu upload được ảnh thì tiếp tục xử lý */
            if ($input && $postion > 0) {
                /* Đóng dấu ảnh và lưu trữ tạm vào @output */
                $output = $dir_upload_image . $image_default;
                self::WatermarkImage($input, $output, $postion);
                /* Resize ảnh upload về kích thước chuẩn trong trong bài viết - Max 640 */
                $resize_upload_dir = self::getDirUpload($key_upload, $created, $size_upload);
                Image::make($output, array('width' => $size_upload))->save($resize_upload_dir . $image_default);
                /* Xóa ảnh đóng dấu lưu trữ */
                if ($output) {
                    @unlink($output);
                }

                return Config::get('cuploads.dir_upload') . $key_upload . '/' . date('Y/m/d', $created) . '/' . Config::get('cuploads.key_size_dir') . $size_upload . '/' . $image_default;
            } else {
                return Config::get('cuploads.dir_upload') . $key_upload . '/' . date('Y/m/d', $created) . '/' . Config::get('cuploads.dir_upload_origin') . $image_default;
            }
        } else {
            return '';
        }
    }

    private static function WatermarkImage($input, $output, $postion = 3, $remove = FALSE) {
        /*
          # Parameters of method apply
          # 1: From image, original image
          # 2: Target image, image destination
          # 3: Watermark image
          # 4: Watermark position number
          #          * 0: Centered
          #      * 1: Top Left
          #      * 2: Top Right
          #      * 3: Footer Right
          #      * 4: Footer left
          #      * 5: Top Centered
          #      * 6: Center Right
          #      * 7: Footer Centered
          #      * 8: Center Left
         */
        require app_path() . '/includes/uploads/watermark.php';
        $upload = new Watermark();
        $upload->apply($input, $output, Config::get('cuploads.water_mark'), $postion);
        if ($remove) {
            @unlink($input);
        }
    }

    /*
     * Lấy đường dẫn tuyệt đối của ảnh được upload
     * @input $file_image : tên file ảnh được upload
     * @input $category : tên thư mục được upload
     * @input $created : thời gian tạo
     * @input $key : kích thước được resize
     */

    public static function getImageUrl($file_image, $key_upload, $key = '', $created = TIME_NOW) {
        $image_upload_config = Config::get('cuploads.config');
        if (isset($image_upload_config[$key_upload])) {
            $category = $image_upload_config[$key_upload]['folder'];
            $resize_config = $image_upload_config[$key_upload]['sizes'];
            /* Lấy đường dẫn ảnh tuyệt đối */
            $dir_upload_static = Config::get('cuploads.dir_upload') . $category . date('Y/m/d', $created) . '/';
            $dir_key = ($key && isset($resize_config[$key])) ? Config::get('cuploads.key_size_dir') . $key . '/' : Config::get('cuploads.dir_upload_origin');
            //return URL::to($dir_upload_static . $dir_key . $file_image);
            return $dir_upload_static . $dir_key . $file_image;
        } else {
            return '';
        }
    }

    /*
     * Chuẩn hóa đường dẫn upload
     * @param $category : thư mục upload ảnh
     * @param $created : thời gian tạo
     * @param $key : kích thước được resize nếu có
     */

    private static function getDirUpload($key_upload, $created = TIME_NOW, $key = '') {
        $image_upload_config = Config::get('cuploads.config');
        if (isset($image_upload_config[$key_upload])) {
            $category = $image_upload_config[$key_upload]['folder'];
            $resize_config = $image_upload_config[$key_upload]['sizes'];
            /* Lấy đường dẫn ảnh tuyệt đối */
            $dir_upload_static = Config::get('cuploads.dir_upload') . $category . date('Y/m/d', $created) . '/';
            $dir_key = ($key && isset($resize_config[$key])) ? 'size' . $key . '/' : '';
            /* Kiểm tra xem thư mục có tồn tại không, nếu không tồn tại thì tạo mới */
            $dir_upload_path = ROOT_PATH . $dir_upload_static . $dir_key;
            FunctionLib::CheckDir($dir_upload_path);
            return $dir_upload_path;
        } else {
            return '';
        }
    }

    private static function getUploadConfig($key_upload) {
        $resize_config = array();
        $image_upload_config = Config::get('cuploads.config');
        if (isset($image_upload_config[$key_upload])) {
            $resize_config = $image_upload_config[$key_upload]['sizes'];
        }
        return $resize_config;
    }

    private static function checkImageUrl($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $code == 200;
    }

    /*
     * Upload to Image lên Imgur
     */

    static public function UploadToImgur($file_upload) {
        $client_id = Config::get('cuploads.imgur_api');
        $handle = fopen($file_upload, "r");
        $data = fread($handle, filesize($file_upload));
        $pvars = array('image' => base64_encode($data));
        $timeout = 60;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://api.imgur.com/3/image.json');
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Client-ID ' . $client_id));
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $pvars);
        $out = curl_exec($curl);
        curl_close($curl);
        $pms = json_decode($out, true);
        $url = $pms['data']['link'];
        if ($url != "") {
            return $url;
        } else {
            return $pms['data']['error'];
        }
    }

    static function TransUploadImage($image_url, $server = 'imgur', $postion = 3) {
        $image_default = '';
        $ext = FunctionLib::getExtension($image_url, 'jpg');
        if (self::checkImageUrl($image_url)) {
            /* Lưu vào hệ thống tạm */
            $file_name = md5(TIME_NOW . rand(0, TIME_NOW));
            $upload_tmp = storage_path() . Config::get('cuploads.dir_upload') . 'tmp-' . $file_name . '.' . $ext;
            $upload_tmp_to = storage_path() . Config::get('cuploads.dir_upload') . $file_name . '.' . $ext;
            file_put_contents($upload_tmp, file($image_url));
            self::WatermarkImage($upload_tmp, $upload_tmp_to, $postion);
            @unlink($upload_tmp);
            /* Upload to Server */
            switch ($server) {
                case 'imgur':
                    $image_default = self::UploadToImgur($upload_tmp_to);
                    break;

                default :
                    $image_default = '';
                    break;
            }
            @unlink($upload_tmp_to);
            return $image_default;
        } else {
            return '';
        }
    }

}
