<?php

use DataDo\Data\Repository;
use DataDo\Slim;
use Slim\App;

require_once 'vendor/autoload.php';

class File
{
    public $id;
    public $filePath;
    public $size;
    public $fileName;
    public $parent;
    public $isFile;
    public $extension;
    public $type;
    public function __construct(SplFileInfo $info = null)
    {
        if ($info !== null) {
            $this->filePath = $info->getRealPath();
            $this->fileName = $info->getBasename();
            $this->size = filesize($this->filePath);
            $this->parent = dirname($this->filePath);
            $this->isFile = $info->isFile();
            $this->extension = $info->getExtension();
            $this->type = $info->getType();
        }
    }
}

$pdo = new PDO('mysql:host=localhost;dbname=file_demo', 'username', 'password');
$repository = new Repository(File::class, $pdo, 'id');


$app = Slim::create($repository);

$app->run();