upload
======

upload for laravel

Upload from file: 

$images = UploadManager::UploadFile('name_file_input', $title_file_name, 'gallery', $created, 0);

Upload from url

$images = UploadManager::UploadUrl($file_url, $title_file_name, 'gallery', $created, 0);
