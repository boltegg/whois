# whois class

PHP class to retrieve WHOIS information.

## Installing

```
composer require boltegg/whois @dev
```

## Methods

#### Get whois information

```php
<?php

$whois = new \Whois();

$domain = 'google.com';

$whois_result = $whois->getInfo($domain);
echo $whois_result;

```

#### Get domain status

```php
<?php

$whois = new \Whois();

if ($whois->isAvailable($domain)) {
    echo "Domain is available\n";
} else {
    echo "Domain is registered\n";
}

```
