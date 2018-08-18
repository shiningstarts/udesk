# Udesk Sdk

## install
```
php composer.phar require shiningstarts/udesk
```
## usage
```php
 //fill out your Udesk account and password in Config.php
 $model = new \Udesk\Request\Ticket();  
 //get ticket list
 $data = $model->getList();
```