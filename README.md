# PhpSms
[![StyleCI](https://styleci.io/repos/44543599/shield)](https://styleci.io/repos/44543599)
[![Build Status](https://travis-ci.org/mikecai/phpsms.svg?branch=master)](https://travis-ci.org/mikecai/phpsms)
[![Latest Stable Version](https://img.shields.io/packagist/v/mikecai/phpsms.svg)](https://packagist.org/packages/mikecai/phpsms)
[![Total Downloads](https://img.shields.io/packagist/dt/mikecai/phpsms.svg)](https://packagist.org/packages/mikecai/phpsms)

可能是目前最聰明，優雅的PHP簡訊發送庫了。

> phpsms的任務均衡調度功能由[toplan/task-balancer](https://github.com/toplan/task-balancer)提供。


# 特色
- 支持一般簡訊
- 支持簡訊發送狀態查詢
- 支持發送均衡調度，可按代理器權重值均衡選擇服務商發送。
- 支持一個或多個備用代理器（服務商）。
- 支持代理器計劃方案熱更新，可隨時更新/刪除/新加代理器。
- 允許推入變量，並自定義本質上實現邏輯（與所屬系統鬆散替換）。
- 靈活的發送前後鉤子。
- [自定義代理器]（＃自定義代理器）和[寄生代理器]（＃寄生代理器）。

# 服务商

| 服務商 | 模板簡訊 | 内容簡訊 | 語音驗證碼 | 最低消费  |  最低消费單價 |
| ----- | :-----: | :-----: | :------: | :-------: | :-----: | :-----:
| [Every8D](http://global.every8d.com.tw/) | × |  √ |  × | NT 8000(1萬條) |  NT0.8/條 
| [亞太企業簡訊(舊)](http://xsms.aptg.com.tw/XSMSAP/userlogin.zul) | × |  √ |  × |(不提供) | (不提供)
| [亞太企業簡訊(新-暫不提供)](https://emanager.aptg.com.tw/konakart/CustomProdIntro.action?page=SmsCost) | × |  √ |  × | NT 8000(1萬條) | NT0.8/條 


# 安装

```php
composer require mikecai/phpsms:~1.8
```

开发中版本
```php
composer require mikecai/phpsms:dev-master
```

# 快速上手

### 1. 配置

- 配置代理器所需參數

為你需要用到的簡訊服務商(即代理器)配置必要的參數。可以在`config\phpsms.php`中鍵為`agents`的數組中配置，也可以手動在程序中設置，示例如下：

```php
//example:
Sms::config([
    'Every8d' => [
        'mdm_number' => 'your_mdm_number',
        'username'  => 'your_username',
        'password'  => 'your_password',
    ],
    'AptgXsms' =>[
        'mdn_number' => 'your_mdm_number', // 手機門號
        'username'  => 'your_username',   //帳號
        'password'  => 'your_password',  //密碼
    ]
]);
```

- 配置代理器調度方案

可在`config\phpsms.php`中鍵為`scheme`的數組中配置。也可以手動在程序中設置，示例如下：

```php
//example:
Sms::scheme([
    //被使用概率为2/3
    'Every8d' => '20',

    //被使用概率為1/3，且为備用代理器
    'AptgXsms' => '10 backup',

    //僅為備用代理器
    'SmsBao' => '0 backup',
]);
```
> **調度方案解析：**
> 如果按照以上配置，那麼系統首次會嘗試使用`Every8d`或`AptgXsms`發送簡訊，且它們被使用的概率分別為`2/3`和`1/3`。
> 如果使用其中一個代理器發送失敗，那麼會啟用備用代理器，按照配置可知備用代理器有`SmsBao`和`SmsBao`，那麼會依次調用直到發送成功或無備用代理器可用。
> 值得注意的是，如果首次嘗試的是`AptgXsms`，那麼備用代理器將會只使用`SmsBao`，也就是會排除使用過的代理器。

### 2. Enjoy it!

```php
require('path/to/vendor/autoload.php');
use mikecai\PhpSms\Sms;

// 接收人手機號
$to = '0987654321';

// 簡訊内容
$content = '【簽名】这是簡訊内容...';

// 使用内容方式發送(如:Every8d)
$result =  Sms::make()->to($to)->content($content)->send();

//$result ，內容會返回 batchId


$batchId = "a7801510-c427-4665-bd8e-ce10be816a1b"; 

//查詢簡訊發送狀態
$sms_status = Sms::status()->query($batchId)->send();

```

### 3. 在laravel和lumen中使用

* 服务提供器

```php
//服务提供器
'providers' => [
    ...
    mikecai\PhpSms\PhpSmsServiceProvider::class,
]

//别名
'aliases' => [
    ...
    'PhpSms' => mikecai\PhpSms\Facades\Sms::class,
]
```

* 生成配置文件

```php
php artisan vendor:publish
```
生成的配置文件為config/phpsms.php，然後在該文件中按提示配置。

* 使用

詳見API，示例：
```php
PhpSms::make()->to($to)->content($content)->send();
```

# API

## API - 全局配置

### Sms::scheme([$name[, $scheme]])

設置/獲取代理器的調度方案。

> 調度配置支持熱更新，即在應用系統的整個運行過程中都能隨時修改。

- 設置

手動設置代理器調度方案(優先級高於配置文件)，如：
```php
Sms::scheme([
    'Every8d' => '80 backup'
    'AptgXsms' => '100 backup'
]);
//或
Sms::scheme('Every8d', '80 backup');
Sms::scheme('AptgXsms', '100 backup');
```
- 获取

通过该方法还能获取所有或指定代理器的调度方案，如：
```php
//获取所有的调度方案:
$scheme = Sms::scheme();

//获取指定代理器的调度方案:
$scheme['SmsBao'] = Sms::scheme('SmsBao');
```

> `scheme`静态方法的更多使用方法见[高级调度配置](#高级调度配置)

### Sms::config([$name[, $config][, $override]]);

设置/获取代理器的配置数据。

> 参数配置支持热更新，即在应用系统的整个运行過程中都能随时修改。

- 設定

手动设置代理器的配置数据(优先级高于配置文件)，如：
```php
Sms::config([
   'Every8d' => [
       'username' => ...,
       'password' => ...,
   ]
]);
//或
Sms::config('Every8d', [
   'username' => ...,
   'password' => ...,
]);
```
- 獲取

通過該方法還能獲取所有或指定代理器的配置參數，如：
```php
//獲取所有的配置：
$config = Sms::config();

//獲取指定代理器的配置：
$config['Every8d'] = Sms::config('Every8d');
```

### Sms::beforeSend($handler[, $override]);

發送前鉤子，示例：
```php
Sms::beforeSend(function($task, $index, $handlers, $prevReturn){
    //獲取簡訊數據
    $smsData = $task->data;
    ...
    //如果返回false會終止發送任務
    return true;
});
```
> 更多細節請查看 [task-balancer](https://github.com/toplan/task-balancer#2-task-lifecycle) 的 `beforeRun` 鉤子

### Sms::beforeAgentSend($handler[, $override]);

代理器發送前鉤子，示例：
```php
Sms::beforeAgentSend(function($task, $driver, $index, $handlers, $prevReturn){
    //簡訊資料:
    $smsData = $task->data;
    //當前使用的代理器名稱：
    $agentName = $driver->name;
    //如果返回false會停止使用當前代理器
    return true;
});
```
> 更多細節請查看 [task-balancer](https://github.com/toplan/task-balancer#2-task-lifecycle) 的 `beforeDriverRun` 鉤子

### Sms::afterAgentSend($handler[, $override]);

代理器發送后鉤子，示例：
```php
Sms::afterAgentSend(function($task, $agentResult, $index, $handlers, $prevReturn){
     //$result为代理器的發送结果数据
     $agentName = $agentResult['driver'];
     ...
});
```
> 更多細節請查看 [task-balancer](https://github.com/toplan/task-balancer#2-task-lifecycle) 的 `afterDriverRun`鉤子

### Sms::afterSend($handler[, $override]);

發送後鉤子，示例：
```php
Sms::afterSend(function($task, $taskResult, $index, $handlers, $prevReturn){
    //$result為發送後獲得的結果數組
    $success = $taskResult['success'];
    ...
});
```
> 更多細節請查看 [task-balancer](https://github.com/toplan/task-balancer#2-task-lifecycle) 的 `afterRun` 鉤子

### Sms::queue([$enable[, $handler]])

該方法可以設置是否啟用鹼性以及定義如何推進到位數。


`$handler`匿名函数可使用的参数:
+ `$sms` : Sms实例
+ `$data` : Sms实例中的簡訊数据，等同于`$sms->all()`

定义如何推送到队列：
```php
//自动启用队列
Sms::queue(function($sms, $data){
    //define how to push to queue.
    ...
});

//第一个参数为true,启用队列
Sms::queue(true, function($sms, $data){
    //define how to push to queue.
    ...
});

//第一个参数为false,暂时关闭队列
Sms::queue(false, function($sms, $data){
    //define how to push to queue.
    ...
});
```

如果已經定義過如何推送到隊列，還可以繼續設置關閉/開啟隊列：
```php
Sms::queue(true);//开启队列
Sms::queue(false);//关闭队列
```

获取队列启用情况：
```php
$enable = Sms::queue();
//为true,表示当前启用了队列。
//为false,表示当前关闭了队列。
```

## API - 發送相关

### Sms::make()

生成發送簡訊的sms实例，并返回实例。
```php
$sms = Sms::make();

//创建实例的同时设置簡訊内容：
$sms = Sms::make('【签名】这是簡訊内容...');

//创建实例的同时设置簡訊模版：
$sms = Sms::make('YunTongXun', 'your_temp_id');
//或
$sms = Sms::make([
    'YunTongXun' => 'your_temp_id',
    'SubMail' => 'your_temp_id',
    ...
]);
```

### Sms::status()->query($batchId)->send();

查詢簡訊發送狀態
```php
//從發送簡訊返回的ID
$batchId = "a7801510-c427-4665-bd8e-ce10be816a1b"; 

//查詢簡訊發送狀態
$sms_status = Sms::status()->query($batchId)->send();

```

### ~~Sms::voice()~~

生成發送语音驗證碼的sms实例，并返回实例。
```php
$sms = Sms::voice();

//创建实例的同时设置驗證碼
$sms = Sms::voice($code);
```

下方為大陸服務，參考就好
> - 如果你使用`Luosimao`语音驗證碼，还需用在配置文件中`Luosimao`选项中设置`voiceApikey`。
> - **语音文件ID**即是在服务商配置的语音文件的唯一编号，比如阿里大鱼[语音通知](http://open.taobao.com/doc2/apiDetail.htm?spm=a219a.7395905.0.0.oORhh9&apiId=25445)的`voice_code`。
> - **模版语音**是另一种语音请求方式，它是通过模版ID和模版数据进行的语音请求，比如阿里大鱼的[文本转语音通知](http://open.taobao.com/doc2/apiDetail.htm?spm=a219a.7395905.0.0.f04PJ3&apiId=25444)。

### type($type)

设置实例类型，可选值有`Sms::TYPE_SMS`和~~Sms::TYPE_VOICE~~(台灣簡訊沒支援)，返回实例对象。

### to($mobile)

设置發送给谁，并返回實例。
```php
$sms->to('0987654321');

```

### data($key, $value)

设置模板簡訊的模板数据，并返回实例对象。
```php
//单个数据
$sms->data('code', $code);

//同时设置多个数据
$sms->data([
    'code' => $code,
    'minutes' => $minutes
]);
```

> 通過`data`方法的組合除了可以實現模版簡訊的數據填充，還可以實現模版語音的數據填充。


### content($text)

设置内容簡訊的内容，并返回实例对象。

> 一些内置的代理器(如SmsBao、YunPian、Luosimao)使用的是内容簡訊(即直接發送簡訊内容)，那么就需要为它们设置簡訊内容。

```php
$sms->content('【签名】这是簡訊内容...');
```


### params($agentName, $params)

直接设置参数到服务商提供的原生接口上，并返回实例对象。
```php
$sms->params('Agent1', [
    'callbackUrl' => ...,
    'userData'    => ...,
]);

//或
$sms->params([
    'Agent1' => [
        'callbackUrl' => ...,
        'userData'    => ...,
    ],
    'Agent2' => [
        ...
    ],
]);
```

### all([$key])

获取Sms实例中的簡訊数据，不带参数时返回所有数据，其结构如下：
```php
[
    'type'      => ...,
    'to'        => ...,
    'data'      => [...], // template data
    'content'   => ...,
    'params'    => [...],
]
```

### agent($name)

临时设置發送时使用的代理器(不会影响备用代理器的正常使用)，并返回实例，`$name`为代理器名称。
```php
$sms->agent('Every8d');
```
> 通过该方法设置的代理器将获得绝对优先权，但只对当前簡訊实例有效。

### send()

请求發送簡訊/语音驗證碼。
```php
//会遵循是否使用队列
$result = $sms->send();

//忽略是否使用队列
$result = $sms->send(true);
```

> `$result`数据结构请参看[task-balancer](https://github.com/toplan/task-balancer)

# 自定义代理器

- step 1

可将配置项(如果有用到)加入到`config/phpsms.php`中键为`agents`的数组里。

```php
//example:
'Foo' => [
    'key' => 'your api key',
    ...
]
```

- step 2

新建一个继承`Toplan\PhpSms\Agent`抽象类的代理器类，建议代理器类名为`FooAgent`，建议命名空间为`Toplan\PhpSms`。

> 如果类名不为`FooAgent`或者命名空间不为`Toplan\PhpSms`，在使用该代理器时则需要指定代理器类，详见[高级调度配置](#高级调度配置)。

- step 3

实现相应的接口，可选的接口有:

| 接口           | 说明         |
| ------------- | :----------: |
| ContentSms    | 發送内容簡訊   |
| TemplateSms   | 發送模版簡訊   |
| VoiceCode     | 發送语音驗證碼 |
| ContentVoice  | 發送内容语音   |
| TemplateVoice | 發送模版语音   |
| FileVoice     | 發送文件语音   |

# 高级调度配置

代理器的高级调度配置可以通过配置文件(`config/phpsms.php`)中的`scheme`项目配置，也可以通过`scheme`静态方法设置。
值得注意的是，高级调度配置的值的数据结构是数组。

### 指定代理器类

如果你自定义了一个代理器，类名不为`FooAgent`或者命名空间不为`Toplan\PhpSms`，
那么你还可以在调度配置时指定你的代理器使用的类。

* 配置方式：

通过配置值中`agentClass`键来指定类名。

* 示例：
```php
Sms::scheme('agentName', [
    '10 backup',
    'agentClass' => 'My\Namespace\MyAgentClass'
]);
```

### 寄生代理器

如果你既不想使用內置的代理器，也不想創建文件寫自定義代理器，那麼寄生代理器或許是個好的選擇，
無需定義代理器類，只需在調度配置時定義好發送簡訊和語音驗證碼的方式即可。

* 配置方式：

可以配置的發送過程有:

| 發送過程           | 参数列表                        | 说明         |
| ----------------- | :---------------------------: | :----------: |
| sendContentSms    | $agent, $to, $content         | 發送内容簡訊   |
| sendTemplateSms   | $agent, $to, $tmpId, $tmpData | 發送模版簡訊   |
| sendVoiceCode     | $agent, $to, $code            | 發送语音驗證碼  |
| sendContentVoice  | $agent, $to, $content         | 發送内容语音   |
| sendTemplateVoice | $agent, $to, $tmpId, $tmpData | 發送模版语音   |
| sendFileVoice     | $agent, $to, $fileId          | 發送文件语音   |

* 示例：
```php
Sms::scheme([
    'agentName' => [
        '20 backup',
        'sendContentSms' => function($agent, $to, $content){
            // 獲取配置(如果設置了的話):
            $key = $agent->key;
            ...
            // 可使用的内置方法:
            $agent->curlGet($url, $params); //get
            $agent->curlPost($url, $params); //post
            ...
            // 更新發送結果:
            $agent->result(Agent::SUCCESS, true);
            $agent->result(Agent::INFO, 'some info');
            $agent->result(Agent::CODE, 'your code');
        },
        'sendVoiceCode' => function($agent, $to, $code){
            // 發送語音驗證碼，同上
        }
    ]
]);
```

# Todo


# License

MIT
