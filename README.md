# cakephp3-blueimp-upload
CakePHP3 plugin allowing to easily use the blueimp jQuery-File-Upload

General Features
----------------
- A helper allows to create an upload button with a progess bar
- A component manage the uploads requests (may be in multiple chunks)
- Each upload is stored into a datatable with the file infos
- Once the upload is over, do what you want in your application with the record and the uploaded file

Installation
------------

### Adding the plugin

You can easily install this plugin using composer as follows:

```bash
composer require alaxos/cakephp3-blueimp-upload
```

### Enabling the plugin

After adding the plugin remember to load it in your `config/bootstrap.php` file.
The `Alaxos` plugin must be loaded as well.

```php
Plugin::load('Alaxos', ['bootstrap' => true]);
Plugin::load('CakephpBlueimpUpload');
```

### Using the plugin

Template
--------
```php
echo $this->BlueimpUpload->chunked('picture_upload', [
                                   'upload_url' => Router::url(['controller' => 'Posts', 'action' => 'upload_picture', $post->id])
                                ]);
```
Check for options in the ```chunked()``` method.

Controller
----------
```php

public $components = ['CakephpBlueimpUpload.Uploader'];
public $helpers    = ['CakephpBlueimpUpload.BlueimpUpload'];

public function upload_picture($id = null)
{
    ...
    
    $upload = $this->Uploader->upload($upload_folder, ['accepted_mimetypes' => ['image/jpeg', 'image/tiff', 'image/png']]);

    if($upload !== false)
    {
        if($upload->complete)
        {
           /*
            * The upload is over. 
            * Do what you want with the $upload entity
            */
        }
    }
    
    ...
}



```
