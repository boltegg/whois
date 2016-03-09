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

$domain = new \Whois('google.com');

$whois = $domain->info();

echo $whois;

```

#### Get domain status

```php

<?php

$domain = new \Whois('google.com');

if ($domain->isAvailable()) {
    echo "Domain is available\n";
} else {
    echo "Domain is registered\n";
}

```
