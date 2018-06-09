#Renewal

##introduce 
这是一个公司项目。用于定期自动续费账号开通的套餐或者按照某些数据触发条件自动退费，停机公司等操作。
使用到了`swoole_timer_tick`，来定期请求数据中心。数据中心取出相对的数据，筛选出合适的数据，处理成相应格式的数据将数据写入`RabbitMQ`消息队列。
然后在消费端，使用`swoole multi processor`启动多个`RabbitMQ`消费者，消费数据。

##how to use
项目使用了`pimple`实现依赖注入。

项目基本的服务，如`Redis`、`DB`、`log`、`config`等默认服务都在`ServiceContainer`中定义注册。

可选服务如`RabbitMQ`的`Producer`和`Customer`，都可以在实例化`ServiceContainer`的时候传入对应的`ServicerProvider`实现注册。

如：
```php
$container = new \App\Utils\ServiceContainer([\App\Utils\Customer\ServiceProvider::class]);
```
这就实现了`Customer`服务的注册，使用时只需要调用对应的`getInstance()`方法，获取服务实例即可；
如：
```php
$customer = $container->customer->getInstance();
```

##helper
**function app($service, $key = '')**

`app()`帮助函数简单化了`Container`的实例，方便依赖注入的使用。
第一个参数`$service`，为需要使用的服务的名称（服务具体名称的定义，参看个服务对应的`ServiceProvider`）。

例如需要一个`redis`实例，可以这样调用：
```php
app('redis');
```

`redis`服务的默认配置为，`config/app.php`中的`redis`数组,如果有另外的配置链接，可以按以下方式调用

```php
 app('redis','xxx');//xxx为`config/app.php`另外一个配置数组。
```

本项目使用package包可参看`composer.json`文件。具体的使用文档参看package的官方文档。

##new a service
每一个服务都需要一个`ServiceProvider`和`Client`。其中`ServiceProvider`需要实现`Pimple\ServiceProviderInterface`接口。`Client`也都需要实现`App\Interfaces\ClientInterface`接口

注册时候，可以按照是否是基础服务，选择加入到`ServiceContainer`中或者，和上面提到的`Producer`服务一样动态注册。


