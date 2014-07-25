# A little script to show how easy it is to spoof a torrent tracker with fake data

This script is just a PoC. Provide your torrent file and use it like this :

```
php composer.phar require devitek/torrent-fake-seeder
```

and then create a php file and put this :

```php
<?php

require_once('vendor/autoload.php');

use Devitek\Net\Torrent\Client\Azureus\Azureus45;
use Devitek\Net\Torrent\Seeder;
use Devitek\Net\Torrent\Torrent;

$torrent = new Torrent('file.torrent');
$client  = new Azureus45();
$seeder  = new Seeder($client, $torrent);

$seeder->bind('update', function ($data) {
    echo $data['uploaded'] . ' MB at ' . $data['speed'] . ' MB/sec uploaded' . PHP_EOL;
});

$seeder->bind('error', function ($data) {
    echo $data['exception']->getMessage() . PHP_EOL;
});

$seeder->seed();
```

This will **fakely** seed your `file.torrent` file.

---

Please use it carefully. Feel free to test, fork and improve it.