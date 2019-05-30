<?php

require_once 'vendor/autoload.php';
require_once "./random_string.php";

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Blob\Models\CreateContainerOptions;
use MicrosoftAzure\Storage\Blob\Models\PublicAccessType;

$connectionString = "DefaultEndpointsProtocol=https;AccountName=evansubmissionduastorage;AccountKey=D23NljMevP00ja9D/m3VAJ6DRfdYK1wUIfiR35jGc7x/Ydhz+BSamoTZniLqnVR3Un3gQouLDWECx7Wy508Jxg==;EndpointSuffix=core.windows.net";

$blobClient = BlobRestProxy::createBlobService($connectionString);

$createContainerOptions = new CreateContainerOptions();
$createContainerOptions->setPublicAccess(PublicAccessType::CONTAINER_AND_BLOBS);
$createContainerOptions->addMetaData("key1", "value1");
$createContainerOptions->addMetaData("key2", "value2");

$containerName = "evansubmitdua".generateRandomString();

$blobClient->createContainer($containerName, $createContainerOptions);

$fileToUpload = false;

if (isset($_POST['submit'])) {

    $fileToUpload = $_FILES["fileToUpload"]["name"];
    $content = fopen($_FILES["fileToUpload"]["tmp_name"], "r");
    echo fread($content, filesize($fileToUpload));
    $blobClient->createBlockBlob($containerName, $fileToUpload, $content);
    header("Location: index.php");
    
}

echo $fileToUpload;

$listBlobsOptions = new ListBlobsOptions();
$listBlobsOptions->setPrefix("");
$result = $blobClient->listBlobs($containerName, $listBlobsOptions);

?>  

<html>

    <head>
    <style>
        th {
            background-color:#707B7C; border-right:solid 1px black; border-bottom:solid 1px black; font-size:8pt; padding5px;font-family: arial; border-top: solid 1px black; border-left: solid 1px black;
        }
        td {
            border-right:solid 1px black; border-bottom:solid 1px black; font-size:8pt; padding:5px; font-family:arial; border-left:solid 1px black; border-top: solid 1px black; text-align: right;
        }
    </style>
    <title>Upload Photo and Computer Vision Analyze</title>
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    </head>

    <body>
        Upload picture that need to be analyzed :
        <form action="index.php" method="post" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" accept=".jpeg,.jpg,.png" required="" id="fileToUpload">
        <input type="submit" name="submit" value="Upload">
        </form>

        <br>
        <br>

        <table>
            <tr>
                <th>File Name</th>
                <th>URL</th>
                <th>Action</th>
            </tr>
        
        <tbody>
            <?php
            do {
                foreach ($result->getBlobs() as $blob) {
            ?>
            <tr>
                <td><?php echo $blob->getName() ?></td>
                <td><?php echo $blob->getUrl()  ?></td>
                <td>
                    <form action="analyze.php" method="post">
                        <input type="hidden" name="url" value="<?php echo $blob->getUrl()?>">
                        <input type="submit" name="submit" value="Lihat" class="btn btn-primary">
                    </form>
                </td>
            </tr>
            <?php
                }
                $listBlobsOptions->setContinuationToken($result->getContinuationToken());
            }
            while ($result->getContinuationToken());
            ?>
        </tbody>
        </table>
    </body>
</html>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script>window.jQuery || document.write('<script src="../../assets/js/vendor/jquery-slim.min.js"><\/script>')</script>