<?php
require __DIR__ . "/AlvineImage.php";
require __DIR__ . "/AlvineImageSet.php";
require __DIR__ . "/MagentoImage.php";
require __DIR__ . "/MagentoImageHelper.php";
require __DIR__ . "/func.php";

$filepath = '/var/www/mey/shared/public/media/imageupdates.txt';
$start = microtime(true);
$imageUrls = readImageDataFromFile($filepath);
$tmp = readImageDataFromFile($filepath);  // wird verwendet um während der Abarbeitung die Quelldatei aktuell zu halten
$current = 0;
$count = count($imageUrls);
$recover = [];

foreach($imageUrls as $articleNumber => $articleImages)
{// prepare Data
  MagentoImageHelper::log(">>>>> " . ++$current . " / " . $count);
  $loop_start = microtime(true);
  $failed = false;

  try
  {
    $set = getImageSet( $articleImages );
    MagentoImageHelper::log("[ENTFERNE ALTE BILDER]");
    $removedImages = deleteImageSet( $set );
    if(0 === $removedImages)
    {
      MagentoImageHelper::log("kein Bilder gefunden...");
    }
    else
    {
      MagentoImageHelper::log(sprintf("es wurden %s Bilder entfernt...", $removedImages) );
    }
    MagentoImageHelper::log("[SPEICHERE NEUE BILDER]");
    importImageSet( $set );
  }
  catch (Exception $e)
  {
    MagentoImageHelper::log("[FEHLER]");
    $recover[$articleNumber] = $articleImages;
    MagentoImageHelper::log(sprintf("Fehler beim Verarbeiten des Bildes: %s", $e->getMessage() ) );
    $failed = true;
  }

  MagentoImageHelper::log(sprintf("Dauer: %s Sekunden", round(microtime(true) - $loop_start) ) );
  
  if($failed === false)
  {// bei erfolgreichen Import den Eintrag aus der Datei entfernen
     MagentoImageHelper::log("[ERFOLGREICH]");
    unset($tmp[$articleNumber]);
    MagentoImageHelper::log("Datei neu geschrieben ( " . writeImageDataToFile($filepath, $tmp) . " Bytes)");
  }
} 

if(!empty($recover))
{
  MagentoImageHelper::log(sprintf("%s Bilder konnten nicht verarbeitet werden", count($recover)));
  file_put_contents(__DIR__ . "/recover.json", json_encode($recover));
}

MagentoImageHelper::log(sprintf("Dauer: %s sek%s", round(microtime(true) - $start)));
