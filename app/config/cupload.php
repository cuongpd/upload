<?php

/**
 * Cấu hình Upload hệ thống
 */
return array(
    /* Cấu hình thư mục Upload */
    'dir_upload' => '/uploads/',
    'water_mark' => ROOT_PATH . 'uploads/watermark.png',
    'key_size_dir' => 'size',
    'dir_upload_origin' => 'origin/',
    'upload_image_from_url_dir' => 'downloads',
    'upload_image_from_url_resize' => 640,
    'config' => array(
        'downloads' => array(/* Upload ảnh từ Url và lưu trữ trên server, phục vụ cho việc lưu trữ ảnh trong bài viết */
            'folder' => 'downloads/',
            'sizes' => array(
                640 => array('width' => 640, 'height' => 0),
            )
        ),
        'gallery' => array(/* Thư viện Gallery */
            'folder' => 'gallery/',
            'sizes' => array(
                640 => array('width' => 640, 'height' => 0),
                320 => array('width' => 320, 'height' => 0),
                160 => array('width' => 160, 'height' => 0)
            )
        ),
        'category' => array(/* Upload Ảnh ở các danh mục */
            'folder' => 'category/',
            'sizes' => array(
                640 => array('width' => 640, 'height' => 0),
                320 => array('width' => 320, 'height' => 0)
            )
        ),
        'tours' => array(/* Thư viện tour */
            'folder' => 'tours/',
            'sizes' => array(
                640 => array('width' => 640, 'height' => 0),
                320 => array('width' => 320, 'height' => 0),
                160 => array('width' => 160, 'height' => 0)
            )
        ),
        'product' => array(
            'folder' => 'product/',
            'sizes' => array(
                120 => array('width' => 120, 'height' => 0),
                240 => array('width' => 240, 'height' => 0),
                320 => array('width' => 320, 'height' => 0),
                480 => array('width' => 480, 'height' => 0),
                640 => array('width' => 640, 'height' => 0),
            ),
        ),
    ),
    /* Cấu hình API Upload to hosting */
    'imgur_api' => 'xxxxx',
);

