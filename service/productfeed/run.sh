#!/bin/bash
 
# dateien älter als 15 tage löschen
find /media/productexport -type f -name "Mey_*.csv.gz" -mtime +15 | xargs rm -f

# alte feeds packen
gzip /media/productexport/Mey_*.csv &> /dev/null

# neuen feed erstellen, log in tmp-vz speichern
php create_feed.php --language=de
php create_feed.php --language=nl
php create_feed.php --language=en
